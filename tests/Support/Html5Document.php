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

namespace PhpMjml\Tests\Support;

use Masterminds\HTML5;

/**
 * Parses HTML the way a browser does, on every supported PHP version.
 *
 * Uses the native HTML5 parser (Dom\HTMLDocument) on PHP 8.4+ and masterminds/html5 before.
 * Security tests need a real HTML5 parser: the legacy libxml HTML parser handles edge cases
 * such as "<!-->" differently from browsers.
 */
final class Html5Document
{
    /**
     * @param list<array{name: string, attributes: list<string>}> $elements
     * @param list<string>                                        $styles   Text content of <style> elements
     * @param list<string>                                        $comments Text content of comments
     */
    private function __construct(
        private readonly array $elements,
        private readonly array $styles,
        private readonly array $comments,
    ) {
    }

    public static function parse(string $html): self
    {
        $html = '<!doctype html>'.$html;

        $document = class_exists(\Dom\HTMLDocument::class)
            ? \Dom\HTMLDocument::createFromString($html, \LIBXML_NOERROR)
            : (new HTML5(['disable_html_ns' => true]))->loadHTML($html);

        $elements = [];
        $styles = [];
        $comments = [];
        self::collect($document, $elements, $styles, $comments);

        return new self($elements, $styles, $comments);
    }

    public function hasElement(string $name): bool
    {
        foreach ($this->elements as $element) {
            if ($element['name'] === $name) {
                return true;
            }
        }

        return false;
    }

    public function hasElementWithAttribute(string $attribute): bool
    {
        foreach ($this->elements as $element) {
            if (\in_array($attribute, $element['attributes'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public function getStyleContents(): array
    {
        return $this->styles;
    }

    /**
     * @return list<string>
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * @param list<array{name: string, attributes: list<string>}> $elements
     * @param list<string>                                        $styles
     * @param list<string>                                        $comments
     */
    private static function collect(\DOMNode|\Dom\Node $node, array &$elements, array &$styles, array &$comments): void
    {
        foreach ($node->childNodes as $child) {
            if ($child instanceof \DOMComment || $child instanceof \Dom\Comment) {
                $comments[] = $child->textContent ?? '';
                continue;
            }

            if ($child instanceof \DOMElement || $child instanceof \Dom\Element) {
                $name = strtolower($child->localName ?? '');
                $attributes = [];
                foreach ($child->attributes as $attribute) {
                    $attributes[] = strtolower($attribute->nodeName ?? '');
                }
                $elements[] = ['name' => $name, 'attributes' => $attributes];

                if ('style' === $name) {
                    $styles[] = $child->textContent ?? '';
                }
            }

            self::collect($child, $elements, $styles, $comments);
        }
    }
}
