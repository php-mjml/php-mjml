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
     * Tags that should capture inner HTML as raw content instead of parsing children.
     * These are components that render their content as HTML directly.
     */
    private const RAW_CONTENT_TAGS = [
        'mj-raw',
        'mj-text',
        'mj-button',
        'mj-navbar-link',
        'mj-accordion-title',
        'mj-accordion-text',
    ];

    public function parse(string $mjml): Node
    {
        // Use PHP 8.4's HTML5 DOM parser which properly handles HTML entities like &nbsp;
        // The HTML5 parser is lenient and handles malformed input gracefully
        libxml_use_internal_errors(true);

        $dom = \Dom\HTMLDocument::createFromString(
            $mjml,
            \LIBXML_NOERROR | \LIBXML_HTML_NOIMPLIED
        );

        $errors = libxml_get_errors();
        libxml_clear_errors();

        $root = $dom->documentElement;
        if (null === $root) {
            throw $this->createParserException($errors, $mjml);
        }

        return $this->parseNode($root);
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
        // Use localName for lowercase tag names (HTML5 parser uppercases tagName)
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
            if (\in_array($tagName, self::RAW_CONTENT_TAGS, true)) {
                $content = $this->getInnerHtml($domNode);
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

    private function getInnerHtml(\Dom\Element $element): string
    {
        // Use the innerHTML property available on Dom\Element
        return trim($element->innerHTML);
    }
}
