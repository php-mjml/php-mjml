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

final class MjmlParser
{
    /**
     * MJML "Ending Tags" - components that contain text/HTML content instead of other MJML tags.
     *
     * These components capture inner HTML as raw content without parsing children.
     * The content remains unprocessed by the MJML engine.
     *
     * @see https://documentation.mjml.io/#ending-tags
     */
    private const ENDING_TAGS = [
        'mj-accordion-text',
        'mj-accordion-title',
        'mj-button',
        'mj-navbar-link',
        'mj-raw',
        'mj-social-element',
        'mj-table',
        'mj-text',
    ];

    /**
     * HTML named entities mapped to their numeric equivalents.
     * XML only supports &amp; &lt; &gt; &quot; &apos; natively.
     *
     * @var array<string, string>
     */
    private const HTML_ENTITY_MAP = [
        '&nbsp;' => '&#160;',
        '&copy;' => '&#169;',
        '&reg;' => '&#174;',
        '&trade;' => '&#8482;',
        '&mdash;' => '&#8212;',
        '&ndash;' => '&#8211;',
        '&lsquo;' => '&#8216;',
        '&rsquo;' => '&#8217;',
        '&ldquo;' => '&#8220;',
        '&rdquo;' => '&#8221;',
        '&bull;' => '&#8226;',
        '&hellip;' => '&#8230;',
        '&euro;' => '&#8364;',
        '&pound;' => '&#163;',
        '&yen;' => '&#165;',
        '&cent;' => '&#162;',
        '&deg;' => '&#176;',
        '&plusmn;' => '&#177;',
        '&times;' => '&#215;',
        '&divide;' => '&#247;',
        '&frac12;' => '&#189;',
        '&frac14;' => '&#188;',
        '&frac34;' => '&#190;',
    ];

    /**
     * @var array<string, string> Placeholder-to-original-content map for mj-raw tags
     */
    private array $rawContents = [];

    public function parse(string $mjml): Node
    {
        // Step 1: Extract mj-raw content and replace with safe placeholders
        // This allows invalid HTML inside mj-raw to pass through XML parsing
        $this->rawContents = [];
        $mjml = $this->extractRawContent($mjml);

        // Step 2: Convert HTML named entities to numeric equivalents for XML compatibility
        $mjml = $this->convertHtmlEntitiesToNumeric($mjml);

        libxml_use_internal_errors(true);

        try {
            // Use XML parser which correctly handles self-closing tags
            $dom = \Dom\XMLDocument::createFromString($mjml, \LIBXML_NOERROR);
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
     * Extract mj-raw content and replace with safe placeholders.
     *
     * This preserves the original content (which may contain invalid XML like
     * HTML void tags, unclosed tags, or unescaped characters) and replaces it
     * with a safe placeholder that the XML parser can handle.
     */
    private function extractRawContent(string $mjml): string
    {
        $counter = 0;
        $rawContents = &$this->rawContents;

        return preg_replace_callback(
            '/<mj-raw([^>]*)>(.*?)<\/mj-raw>/s',
            static function (array $matches) use (&$rawContents, &$counter): string {
                $attrs = $matches[1];
                $content = $matches[2];
                $placeholder = "__MJML_RAW_{$counter}__";
                $rawContents[$placeholder] = $content;
                ++$counter;

                return "<mj-raw{$attrs}>{$placeholder}</mj-raw>";
            },
            $mjml
        ) ?? $mjml;
    }

    /**
     * Prepare MJML for XML parsing by handling entities properly.
     *
     * XML only recognizes &amp; &lt; &gt; &quot; &apos; as named entities.
     * This method:
     * 1. Converts HTML named entities to numeric equivalents
     * 2. Escapes bare ampersands that are not part of valid entity references
     */
    private function convertHtmlEntitiesToNumeric(string $mjml): string
    {
        // First, convert known HTML entities to numeric
        $mjml = str_replace(
            array_keys(self::HTML_ENTITY_MAP),
            array_values(self::HTML_ENTITY_MAP),
            $mjml
        );

        // Escape bare ampersands that are not part of valid entity references
        // Valid entities: &name; or &#123; or &#x1A;
        // We match & not followed by: word chars + semicolon, or # + digits + semicolon, or #x + hex + semicolon
        $mjml = preg_replace(
            '/&(?!(?:[a-zA-Z][a-zA-Z0-9]*|#[0-9]+|#x[0-9a-fA-F]+);)/',
            '&amp;',
            $mjml
        ) ?? $mjml;

        return $mjml;
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

    private function parseNode(\Dom\Node $domNode): Node
    {
        $tagName = $domNode instanceof \Dom\Element ? $domNode->localName : $domNode->nodeName;
        /** @var array<string, string> $attributes */
        $attributes = [];
        /** @var array<Node> $children */
        $children = [];
        $content = '';

        if ($domNode instanceof \Dom\Element) {
            // Parse attributes
            foreach ($domNode->attributes as $attr) {
                $attributes[$attr->nodeName] = $attr->nodeValue ?? '';
            }

            // For raw content tags, get inner HTML directly without parsing children
            if (\in_array($tagName, self::ENDING_TAGS, true)) {
                $content = $this->getInnerHtml($domNode);

                // For mj-raw, restore the original content from placeholder
                if ('mj-raw' === $tagName) {
                    $content = $this->restoreRawContent($content);
                }
            } else {
                // Parse children
                foreach ($domNode->childNodes as $child) {
                    if ($child instanceof \Dom\Element) {
                        $children[] = $this->parseNode($child);
                    } elseif ($child instanceof \Dom\Text) {
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
     * Restore original raw content from placeholder.
     *
     * Note: Only trims for placeholder matching, but preserves original content exactly.
     */
    private function restoreRawContent(string $content): string
    {
        $trimmedContent = trim($content);

        // Check if content is a placeholder
        if (isset($this->rawContents[$trimmedContent])) {
            // Return the original content without trimming to preserve whitespace
            return $this->rawContents[$trimmedContent];
        }

        return $content;
    }

    private function getInnerHtml(\Dom\Element $element): string
    {
        // Use the innerHTML property available on Dom\Element
        return trim($element->innerHTML);
    }
}
