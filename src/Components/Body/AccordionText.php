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
use PhpMjml\Component\Context\AccordionContextResolver;

final class AccordionText extends BodyComponent
{
    private const DEFAULT_FONT_SIZE = '13px';
    private const DEFAULT_LINE_HEIGHT = '1';
    private const DEFAULT_PADDING = '16px';

    protected static bool $endingTag = true;

    public static function getComponentName(): string
    {
        return 'mj-accordion-text';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'background-color' => 'color',
            'font-size' => 'unit(px)',
            'font-family' => 'string',
            'font-weight' => 'string',
            'letter-spacing' => 'unitWithNegative(px,em)',
            'line-height' => 'unit(px,%,)',
            'color' => 'color',
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
            'line-height' => self::DEFAULT_LINE_HEIGHT,
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
                'background' => $this->getAttribute('background-color'),
                'font-size' => $this->getAttribute('font-size'),
                'font-family' => $this->resolveFontFamily(),
                'font-weight' => $this->getAttribute('font-weight'),
                'letter-spacing' => $this->getAttribute('letter-spacing'),
                'line-height' => $this->getAttribute('line-height'),
                'color' => $this->getAttribute('color'),
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
        ];
    }

    public function render(): string
    {
        return \sprintf(
            '<div %s><table %s><tbody><tr>%s</tr></tbody></table></div>',
            $this->htmlAttributes([
                'class' => 'mj-accordion-content',
            ]),
            $this->htmlAttributes([
                'cellspacing' => '0',
                'cellpadding' => '0',
                'style' => 'table',
            ]),
            $this->renderContent(),
        );
    }

    private function renderContent(): string
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

    private function resolveFontFamily(): ?string
    {
        // First check if explicitly set on this component
        $fontFamily = $this->getAttribute('font-family');
        if (null !== $fontFamily) {
            return $fontFamily;
        }

        $settings = $this->getAccordionSettings();
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
        $settings = $this->getAccordionSettings();
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

    /**
     * Get accordion settings from parent context.
     *
     * @return array<string, string|null>|null
     */
    private function getAccordionSettings(): ?array
    {
        return $this->context?->getComponentData(AccordionContextResolver::KEY);
    }
}
