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

final class MjmlParser
{
    private XmlPreprocessorInterface $xmlPreprocessor;

    private ?Registry $registry;

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

    public function __construct(
        ?XmlPreprocessorInterface $xmlPreprocessor = null,
        ?Registry $registry = null,
    ) {
        $this->xmlPreprocessor = $xmlPreprocessor ?? new XmlEntityPreprocessor();
        $this->registry = $registry;
    }

    public function parse(string $mjml): Node
    {
        // Step 1: Extract ending tag content and replace with safe placeholders
        // This allows invalid HTML inside ending tags to pass through XML parsing
        $this->endingTagContents = [];
        $mjml = $this->extractEndingTagContents($mjml);

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
     * Get ending tag names from Registry or fall back to defaults.
     *
     * @return list<string>
     */
    private function getEndingTags(): array
    {
        if (null !== $this->endingTags) {
            return $this->endingTags;
        }

        $this->endingTags = $this->registry?->getEndingTagNames()
            ?? Registry::DEFAULT_ENDING_TAGS;

        return $this->endingTags;
    }

    /**
     * Check if the new PHP 8.4+ DOM API is available.
     */
    private static function useNewDomApi(): bool
    {
        static $useNew;

        return $useNew ??= class_exists(\Dom\XMLDocument::class);
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
        $counter = 0;
        $endingTagContents = &$this->endingTagContents;

        // Build regex for all ending tags
        // Use negative lookbehind (?<!\/) to exclude self-closing tags like <mj-text />
        // Use \s* before closing > to handle tags split across lines like </mj-text\n      >
        $tagPattern = implode('|', array_map('preg_quote', $this->getEndingTags()));
        $pattern = '/<('.$tagPattern.')([^>]*)(?<!\/)>(.*?)<\/\1\s*>/s';

        return preg_replace_callback(
            $pattern,
            static function (array $matches) use (&$endingTagContents, &$counter): string {
                $tagName = $matches[1];
                $attrs = $matches[2];
                $content = $matches[3];
                $placeholder = "__MJML_ENDING_{$counter}__";
                $endingTagContents[$placeholder] = $content;
                ++$counter;

                return "<{$tagName}{$attrs}>{$placeholder}</{$tagName}>";
            },
            $mjml
        ) ?? $mjml;
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

                    if ($isChildElement) {
                        $children[] = $this->parseNode($child);
                    } elseif ($isChildText) {
                        $text = trim($child->textContent ?? '');
                        if ('' !== $text) {
                            $content .= $text;
                        }
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
