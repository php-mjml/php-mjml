<?php

declare(strict_types=1);

/*
 * This file is part of the PHP-MJML package.
 *
 * (c) David Gorges
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMjml\Parser;

use PhpMjml\Component\Registry;
use PhpMjml\Components\Body\Raw;
use PhpMjml\Preset\CorePreset;

final class MjmlParser
{
    /**
     * Matches a document type declaration. DTDs are never needed for MJML and allow
     * entity-expansion attacks (e.g. quadratic blowup), so documents declaring one are rejected.
     */
    private const DOCTYPE_PATTERN = '/<!DOCTYPE/i';

    /**
     * Comment text that HTML5 parsers treat as an abruptly closed comment ("<!-->" or "<!--->"),
     * which would turn the rest of the comment into live markup when re-emitted.
     */
    private const UNSAFE_COMMENT_START_PATTERN = '/^-?>/';

    private XmlPreprocessorInterface $xmlPreprocessor;

    private ?Registry $registry;

    private static ?bool $useNewDomApi = null;

    /**
     * @var array<string, string> Placeholder-to-original-content map for ending tags
     */
    private array $endingTagContents = [];

    /**
     * Cached ending tags from the registry.
     *
     * @var list<string>|null
     */
    private ?array $endingTags = null;

    private bool $keepComments = true;

    public function __construct(
        ?XmlPreprocessorInterface $xmlPreprocessor = null,
        ?Registry $registry = null,
    ) {
        $this->xmlPreprocessor = $xmlPreprocessor ?? new XmlEntityPreprocessor();
        $this->registry = $registry;
    }

    /**
     * @param bool $keepComments Whether XML comments are kept as mj-raw nodes (like the JS keepComments option)
     *
     * @throws ParserException
     */
    public function parse(string $mjml, bool $keepComments = true): Node
    {
        // Step 1: Extract ending tag content and replace with safe placeholders
        // This allows invalid HTML inside ending tags to pass through XML parsing
        $this->endingTagContents = [];
        $this->keepComments = $keepComments;
        $mjml = $this->extractEndingTagContents($mjml);

        // Reject DTDs before libxml sees them: internal entities are expanded at parse time
        if (1 === preg_match(self::DOCTYPE_PATTERN, $mjml)) {
            throw new ParserException('Invalid MJML: document type declarations (<!DOCTYPE>) are not allowed');
        }

        // Step 2: Preprocess XML for compatibility (convert HTML entities, escape ampersands, etc.)
        $mjml = $this->xmlPreprocessor->preprocess($mjml);

        libxml_use_internal_errors(true);

        try {
            // Use XML parser which correctly handles self-closing tags
            if (self::useNewDomApi()) {
                // PHP 8.4+: Use the new Dom\XMLDocument API
                $dom = \Dom\XMLDocument::createFromString($mjml, \LIBXML_NOERROR);
            } else {
                // PHP 8.2/8.3: Use legacy DOMDocument API
                $dom = new \DOMDocument();
                $dom->loadXML($mjml, \LIBXML_NOERROR);
            }
        } catch (\DOMException $e) {
            libxml_clear_errors();
            throw new ParserException('Failed to parse MJML: '.$e->getMessage(), 0, $e);
        }

        $errors = libxml_get_errors();
        libxml_clear_errors();

        $root = $dom->documentElement;
        if (null === $root) {
            throw $this->createParserException($errors, $mjml);
        }

        return $this->parseNode($root);
    }

    /**
     * Get ending tag names from Registry or fall back to the core components.
     *
     * @return list<string>
     */
    private function getEndingTags(): array
    {
        return $this->endingTags ??= $this->registry?->getEndingTagNames()
            ?? CorePreset::getEndingTagNames();
    }

    /**
     * Check if the new PHP 8.4+ DOM API is available.
     */
    private static function useNewDomApi(): bool
    {
        return self::$useNewDomApi ??= class_exists(\Dom\XMLDocument::class);
    }

    /**
     * Extract ending tag content and replace with safe placeholders.
     *
     * This preserves the original content (which may contain invalid XML like
     * HTML void tags, unclosed tags, or unescaped characters) and replaces it
     * with a safe placeholder that the XML parser can handle.
     */
    private function extractEndingTagContents(string $mjml): string
    {
        $openTags = $this->getEndingTags();
        $result = '';
        $offset = 0;
        $counter = 0;
        $length = \strlen($mjml);

        // Scan linearly: each position is visited a bounded number of times. Once a tag has no
        // closing tag after some position, it cannot have one after any later position either,
        // so it is removed from the search instead of being re-scanned (which would be quadratic).
        $openPattern = self::buildOpeningTagPattern($openTags);

        while ([] !== $openTags && $offset < $length) {
            $matched = preg_match($openPattern, $mjml, $open, \PREG_OFFSET_CAPTURE, $offset);
            if (false === $matched) {
                throw $this->createPcreException();
            }
            if (0 === $matched) {
                break;
            }

            [$fullOpen, $openPos] = $open[0];
            $tagName = $open[1][0];
            $attrs = $open[2][0];
            $contentStart = $openPos + \strlen($fullOpen);

            // Self-closing tag (<mj-text />): leave it to the XML parser
            if (str_ends_with($attrs, '/')) {
                $result .= substr($mjml, $offset, $contentStart - $offset);
                $offset = $contentStart;
                continue;
            }

            $closePattern = '/<\/'.preg_quote($tagName, '/').'\s*>/';
            $closed = preg_match($closePattern, $mjml, $close, \PREG_OFFSET_CAPTURE, $contentStart);
            if (false === $closed) {
                throw $this->createPcreException();
            }
            if (0 === $closed) {
                // No closing tag anywhere after this point: stop looking for this tag
                $openTags = array_values(array_diff($openTags, [$tagName]));
                $openPattern = self::buildOpeningTagPattern($openTags);
                $result .= substr($mjml, $offset, $contentStart - $offset);
                $offset = $contentStart;
                continue;
            }

            [$fullClose, $closePos] = $close[0];
            $placeholder = "__MJML_ENDING_{$counter}__";
            $this->endingTagContents[$placeholder] = substr($mjml, $contentStart, $closePos - $contentStart);
            ++$counter;

            $result .= substr($mjml, $offset, $openPos - $offset)."<{$tagName}{$attrs}>{$placeholder}</{$tagName}>";
            $offset = $closePos + \strlen($fullClose);
        }

        return $result.substr($mjml, $offset);
    }

    /**
     * Build the pattern matching the opening tag of any of the given ending tags.
     *
     * The tag name must be followed by whitespace, "/" or ">" so that <mj-text> does not match <mj-textfoo>.
     *
     * @param list<string> $tagNames
     */
    private static function buildOpeningTagPattern(array $tagNames): string
    {
        $alternatives = implode('|', array_map(static fn (string $tag): string => preg_quote($tag, '/'), $tagNames));

        return '/<('.$alternatives.')(?=[\s\/>])([^>]*)>/';
    }

    private function createPcreException(): ParserException
    {
        return new ParserException('Failed to parse MJML: '.preg_last_error_msg());
    }

    /**
     * Create a detailed parser exception from libxml errors.
     *
     * @param \LibXMLError[] $errors
     */
    private function createParserException(array $errors, string $mjml): ParserException
    {
        if ([] === $errors) {
            return new ParserException('Invalid MJML: no root element found');
        }

        $messages = [];
        $lines = explode("\n", $mjml);

        foreach ($errors as $error) {
            $level = match ($error->level) {
                \LIBXML_ERR_WARNING => 'Warning',
                \LIBXML_ERR_ERROR => 'Error',
                \LIBXML_ERR_FATAL => 'Fatal',
                default => 'Unknown',
            };

            $lineContent = '';
            if ($error->line > 0 && isset($lines[$error->line - 1])) {
                $lineContent = ' → '.trim($lines[$error->line - 1]);
            }

            $messages[] = \sprintf(
                '%s on line %d: %s%s',
                $level,
                $error->line,
                trim($error->message),
                $lineContent
            );
        }

        // Limit to first 5 errors to avoid overwhelming output
        $displayMessages = \array_slice($messages, 0, 5);
        if (\count($messages) > 5) {
            $displayMessages[] = \sprintf('... and %d more errors', \count($messages) - 5);
        }

        return new ParserException(
            'Failed to parse MJML: '.implode('; ', $displayMessages)
        );
    }

    /**
     * @param \Dom\Node|\DOMNode $domNode
     */
    private function parseNode(object $domNode): Node
    {
        $isElement = $domNode instanceof \Dom\Element || $domNode instanceof \DOMElement;
        $tagName = ($isElement ? $domNode->localName : $domNode->nodeName) ?? '';
        /** @var array<string, string> $attributes */
        $attributes = [];
        /** @var array<Node> $children */
        $children = [];
        $content = '';

        if ($isElement) {
            \assert($domNode instanceof \Dom\Element || $domNode instanceof \DOMElement);

            // Parse attributes
            foreach ($domNode->attributes as $attr) {
                $attributes[$attr->nodeName] = $attr->nodeValue ?? '';
            }

            // For raw content tags, get inner HTML directly without parsing children
            if (\in_array($tagName, $this->getEndingTags(), true)) {
                $content = $this->getInnerHtml($domNode);

                // Restore the original content from placeholder
                $content = $this->restoreEndingTagContent($content);
            } else {
                // Parse children
                foreach ($domNode->childNodes as $child) {
                    $isChildElement = $child instanceof \Dom\Element || $child instanceof \DOMElement;
                    $isChildText = $child instanceof \Dom\Text || $child instanceof \DOMText;
                    $isChildComment = $child instanceof \Dom\Comment || $child instanceof \DOMComment;

                    if ($isChildElement) {
                        $children[] = $this->parseNode($child);
                    } elseif ($isChildText) {
                        $text = trim($child->textContent ?? '');
                        if ('' !== $text) {
                            $content .= $text;
                        }
                    } elseif ($isChildComment && $this->keepComments) {
                        // Comments are kept as mj-raw nodes, matching the JS parser
                        $children[] = new Node(
                            tagName: Raw::getComponentName(),
                            attributes: [],
                            children: [],
                            content: '<!--'.$this->sanitizeCommentText($child->textContent ?? '').'-->',
                        );
                    }
                }
            }
        }

        return new Node(
            tagName: $tagName,
            attributes: $attributes,
            children: $children,
            content: $content,
        );
    }

    /**
     * Make comment text safe to re-emit inside "<!--" and "-->".
     *
     * XML allows a comment to start with ">" or "->", but HTML5 parses "<!-->" and "<!--->"
     * as complete (empty) comments, so the remaining text would become live markup.
     */
    private function sanitizeCommentText(string $text): string
    {
        if (1 === preg_match(self::UNSAFE_COMMENT_START_PATTERN, $text)) {
            return ' '.$text;
        }

        return $text;
    }

    /**
     * Restore original ending tag content from placeholder.
     *
     * Note: Only trims for placeholder matching, but preserves original content exactly.
     */
    private function restoreEndingTagContent(string $content): string
    {
        $trimmedContent = trim($content);

        // Check if content is a placeholder
        if (isset($this->endingTagContents[$trimmedContent])) {
            // Return the original content without trimming to preserve whitespace
            return $this->endingTagContents[$trimmedContent];
        }

        return $content;
    }

    /**
     * @param \Dom\Element|\DOMElement $element
     */
    private function getInnerHtml(object $element): string
    {
        // PHP 8.4+: Use the innerHTML property available on Dom\Element
        if ($element instanceof \Dom\Element) {
            return trim($element->innerHTML);
        }

        // PHP 8.2/8.3: Polyfill innerHTML for legacy DOMElement
        $innerHTML = '';
        $ownerDocument = $element->ownerDocument;
        if (null !== $ownerDocument) {
            foreach ($element->childNodes as $child) {
                $innerHTML .= $ownerDocument->saveXML($child);
            }
        }

        return trim($innerHTML);
    }
}
