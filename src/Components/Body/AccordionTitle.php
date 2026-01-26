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

namespace PhpMjml\Components\Body;

use PhpMjml\Component\BodyComponent;
use PhpMjml\Helper\ConditionalTag;

final class AccordionTitle extends BodyComponent
{
    private const DEFAULT_FONT_SIZE = '13px';
    private const DEFAULT_PADDING = '16px';

    protected static bool $endingTag = true;

    public static function getComponentName(): string
    {
        return 'mj-accordion-title';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'background-color' => 'color',
            'color' => 'color',
            'font-size' => 'unit(px)',
            'font-family' => 'string',
            'font-weight' => 'string',
            'padding-bottom' => 'unit(px,%)',
            'padding-left' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'padding-top' => 'unit(px,%)',
            'padding' => 'unit(px,%){1,4}',
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'font-size' => self::DEFAULT_FONT_SIZE,
            'padding' => self::DEFAULT_PADDING,
        ];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        return [
            'td' => [
                'width' => '100%',
                'background-color' => $this->getAttribute('background-color'),
                'color' => $this->getAttribute('color'),
                'font-size' => $this->getAttribute('font-size'),
                'font-family' => $this->resolveFontFamily(),
                'font-weight' => $this->getAttribute('font-weight'),
                'padding' => $this->getAttribute('padding'),
                'padding-bottom' => $this->getAttribute('padding-bottom'),
                'padding-left' => $this->getAttribute('padding-left'),
                'padding-right' => $this->getAttribute('padding-right'),
                'padding-top' => $this->getAttribute('padding-top'),
            ],
            'table' => [
                'width' => '100%',
                'border-bottom' => $this->getIconAttribute('border'),
            ],
            'td2' => [
                'padding' => '16px',
                'background' => $this->getAttribute('background-color'),
                'vertical-align' => $this->getIconAttribute('icon-align'),
            ],
            'img' => [
                'display' => 'none',
                'width' => $this->getIconAttribute('icon-width'),
                'height' => $this->getIconAttribute('icon-height'),
            ],
        ];
    }

    public function render(): string
    {
        $contentElements = [$this->renderTitle(), $this->renderIcons()];

        $iconPosition = $this->getIconAttribute('icon-position');
        if ('right' !== $iconPosition) {
            $contentElements = array_reverse($contentElements);
        }

        $content = implode("\n", $contentElements);

        return \sprintf(
            '<div %s><table %s><tbody><tr>%s</tr></tbody></table></div>',
            $this->htmlAttributes(['class' => 'mj-accordion-title']),
            $this->htmlAttributes([
                'cellspacing' => '0',
                'cellpadding' => '0',
                'style' => 'table',
            ]),
            $content,
        );
    }

    private function renderTitle(): string
    {
        // Add spaces around content to match JS template literal formatting
        return \sprintf(
            '<td %s> %s </td>',
            $this->htmlAttributes([
                'class' => $this->getAttribute('css-class'),
                'style' => 'td',
            ]),
            $this->getContent(),
        );
    }

    private function renderIcons(): string
    {
        $iconHtml = \sprintf(
            '<td %s><img %s /><img %s /></td>',
            $this->htmlAttributes([
                'class' => 'mj-accordion-ico',
                'style' => 'td2',
            ]),
            $this->htmlAttributes([
                'src' => $this->getIconAttribute('icon-wrapped-url'),
                'alt' => $this->getIconAttribute('icon-wrapped-alt'),
                'class' => 'mj-accordion-more',
                'style' => 'img',
            ]),
            $this->htmlAttributes([
                'src' => $this->getIconAttribute('icon-unwrapped-url'),
                'alt' => $this->getIconAttribute('icon-unwrapped-alt'),
                'class' => 'mj-accordion-less',
                'style' => 'img',
            ]),
        );

        return ConditionalTag::wrap($iconHtml, true);
    }

    private function resolveFontFamily(): ?string
    {
        // First check if explicitly set on this component
        $fontFamily = $this->getAttribute('font-family');
        if (null !== $fontFamily) {
            return $fontFamily;
        }

        $settings = $this->context?->accordionSettings;
        if (null === $settings) {
            return null;
        }

        // Check element font family (from AccordionElement)
        $elementFontFamily = $settings['elementFontFamily'] ?? null;
        if (null !== $elementFontFamily) {
            return $elementFontFamily;
        }

        // Check accordion font family (from Accordion)
        $fontFamily = $settings['fontFamily'] ?? null;
        if (null !== $fontFamily) {
            return $fontFamily;
        }

        return null;
    }

    private function getIconAttribute(string $name): ?string
    {
        // First check own attributes
        $value = $this->getAttribute($name);
        if (null !== $value) {
            return $value;
        }

        // Fall back to parent context
        $settings = $this->context?->accordionSettings;
        if (null === $settings) {
            return null;
        }

        return match ($name) {
            'border' => $settings['border'] ?? null,
            'icon-align' => $settings['iconAlign'] ?? null,
            'icon-width' => $settings['iconWidth'] ?? null,
            'icon-height' => $settings['iconHeight'] ?? null,
            'icon-position' => $settings['iconPosition'] ?? null,
            'icon-wrapped-url' => $settings['iconWrappedUrl'] ?? null,
            'icon-wrapped-alt' => $settings['iconWrappedAlt'] ?? null,
            'icon-unwrapped-url' => $settings['iconUnwrappedUrl'] ?? null,
            'icon-unwrapped-alt' => $settings['iconUnwrappedAlt'] ?? null,
            default => null,
        };
    }
}
