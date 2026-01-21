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

namespace PhpMjml\Component;

use PhpMjml\Helper\BorderParser;
use PhpMjml\Helper\ShorthandParser;

abstract class BodyComponent extends AbstractComponent
{
    protected static bool $endingTag = false;
    protected static bool $rawElement = false;

    public static function isEndingTag(): bool
    {
        return static::$endingTag;
    }

    public static function isRawElement(): bool
    {
        return static::$rawElement;
    }

    /**
     * Returns CSS styles for this component.
     *
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        return [];
    }

    /**
     * Get the context to pass to child components.
     *
     * @return array<string, mixed>
     */
    public function getChildContext(): array
    {
        return $this->context?->toArray() ?? [];
    }

    /**
     * Get a shorthand attribute value for a specific direction.
     *
     * @param string $attribute Base attribute name (e.g., 'padding')
     * @param string $direction Direction ('top', 'right', 'bottom', 'left')
     */
    protected function getShorthandAttrValue(string $attribute, string $direction): int
    {
        $directionAttr = $this->getAttribute("{$attribute}-{$direction}");

        if (null !== $directionAttr && '' !== $directionAttr) {
            return (int) $directionAttr;
        }

        $baseAttr = $this->getAttribute($attribute);
        if (null === $baseAttr || '' === $baseAttr) {
            return 0;
        }

        return ShorthandParser::parse((string) $baseAttr, $direction);
    }

    /**
     * Get the border width for a specific direction.
     *
     * @param string $direction Direction ('top', 'right', 'bottom', 'left')
     * @param string $attribute Base attribute name (default: 'border')
     */
    protected function getShorthandBorderValue(string $direction, string $attribute = 'border'): int
    {
        $borderDirection = $this->getAttribute("{$attribute}-{$direction}");
        $border = $this->getAttribute($attribute);

        return BorderParser::parse((string) ($borderDirection ?? $border ?? '0'));
    }

    /**
     * Calculate box model widths (total, borders, paddings, box content).
     *
     * @return array{totalWidth: int, borders: int, paddings: int, box: int}
     */
    protected function getBoxWidths(): array
    {
        $containerWidth = (null !== $this->context) ? $this->context->containerWidth : 600;
        $parsedWidth = (int) $containerWidth;

        $paddings = $this->getShorthandAttrValue('padding', 'right')
            + $this->getShorthandAttrValue('padding', 'left');

        $borders = $this->getShorthandBorderValue('right')
            + $this->getShorthandBorderValue('left');

        return [
            'totalWidth' => $parsedWidth,
            'borders' => $borders,
            'paddings' => $paddings,
            'box' => $parsedWidth - $paddings - $borders,
        ];
    }

    /**
     * Builds an HTML attribute string from an array of attributes.
     * If the 'style' key contains a string, it's treated as a style key from getStyles().
     *
     * @param array<string, string|bool|int|array<string, string|null>|null> $attributes
     */
    protected function htmlAttributes(array $attributes): string
    {
        $result = [];

        foreach ($attributes as $key => $value) {
            if (null === $value || false === $value) {
                continue;
            }

            if (true === $value) {
                $result[] = $key;
                continue;
            }

            // Handle style attribute specially - can be string key or array
            if ('style' === $key) {
                if (\is_string($value)) {
                    $styles = $this->getStyles()[$value] ?? [];
                    $value = $this->inlineStyles($styles);
                } elseif (\is_array($value)) {
                    $value = $this->inlineStyles($value);
                }

                if ('' === $value) {
                    continue;
                }
            } elseif (\is_array($value)) {
                // Skip arrays for non-style attributes
                continue;
            }

            $result[] = \sprintf('%s="%s"', $key, htmlspecialchars((string) $value, \ENT_QUOTES, 'UTF-8'));
        }

        return implode(' ', $result);
    }

    /**
     * Converts a styles array to an inline CSS string.
     *
     * @param array<string, string|null> $styles
     */
    protected function inlineStyles(array $styles): string
    {
        $result = [];

        foreach ($styles as $property => $value) {
            if (null === $value || '' === $value) {
                continue;
            }

            $result[] = \sprintf('%s:%s', $property, $value);
        }

        return implode(';', $result);
    }

    /**
     * Renders child components.
     */
    protected function renderChildren(): string
    {
        $output = '';

        foreach ($this->children as $child) {
            if ($child instanceof ComponentInterface) {
                $output .= $child->render();
            }
        }

        return $output;
    }
}
