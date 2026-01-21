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
use PhpMjml\Helper\WidthParser;

final class Image extends BodyComponent
{
    protected static bool $endingTag = true;

    public static function getComponentName(): string
    {
        return 'mj-image';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'alt' => 'string',
            'href' => 'string',
            'name' => 'string',
            'src' => 'string',
            'srcset' => 'string',
            'sizes' => 'string',
            'title' => 'string',
            'rel' => 'string',
            'align' => 'enum(left,center,right)',
            'border' => 'string',
            'border-bottom' => 'string',
            'border-left' => 'string',
            'border-right' => 'string',
            'border-top' => 'string',
            'border-radius' => 'unit(px,%){1,4}',
            'container-background-color' => 'color',
            'fluid-on-mobile' => 'boolean',
            'padding' => 'unit(px,%){1,4}',
            'padding-bottom' => 'unit(px,%)',
            'padding-left' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'padding-top' => 'unit(px,%)',
            'target' => 'string',
            'width' => 'unit(px)',
            'height' => 'unit(px,auto)',
            'max-height' => 'unit(px,%)',
            'font-size' => 'unit(px)',
            'usemap' => 'string',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'alt' => '',
            'align' => 'center',
            'border' => '0',
            'height' => 'auto',
            'padding' => '10px 25px',
            'target' => '_blank',
            'font-size' => '13px',
        ];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        $width = $this->getContentWidth();
        $fullWidth = 'full-width' === $this->getAttribute('full-width');

        $parsed = WidthParser::parse((string) $width);
        $parsedWidth = $parsed['parsedWidth'];
        $unit = $parsed['unit'];

        return [
            'img' => [
                'border' => $this->getAttribute('border'),
                'border-left' => $this->getAttribute('border-left'),
                'border-right' => $this->getAttribute('border-right'),
                'border-top' => $this->getAttribute('border-top'),
                'border-bottom' => $this->getAttribute('border-bottom'),
                'border-radius' => $this->getAttribute('border-radius'),
                'display' => 'block',
                'outline' => 'none',
                'text-decoration' => 'none',
                'height' => $this->getAttribute('height'),
                'max-height' => $this->getAttribute('max-height'),
                'min-width' => $fullWidth ? '100%' : null,
                'width' => '100%',
                'max-width' => $fullWidth ? '100%' : null,
                'font-size' => $this->getAttribute('font-size'),
            ],
            'td' => [
                'width' => $fullWidth ? null : $parsedWidth.$unit,
            ],
            'table' => [
                'min-width' => $fullWidth ? '100%' : null,
                'max-width' => $fullWidth ? '100%' : null,
                'width' => $fullWidth ? $parsedWidth.$unit : null,
                'border-collapse' => 'collapse',
                'border-spacing' => '0px',
            ],
        ];
    }

    public function render(): string
    {
        $this->addFluidMobileStyle();

        $fluidClass = $this->getAttribute('fluid-on-mobile') ? 'mj-full-width-mobile' : null;

        return \sprintf(
            '<table %s><tbody><tr><td %s>%s</td></tr></tbody></table>',
            $this->htmlAttributes([
                'border' => '0',
                'cellpadding' => '0',
                'cellspacing' => '0',
                'role' => 'presentation',
                'style' => 'table',
                'class' => $fluidClass,
            ]),
            $this->htmlAttributes([
                'style' => 'td',
                'class' => $fluidClass,
            ]),
            $this->renderImage()
        );
    }

    private function renderImage(): string
    {
        $height = $this->getAttribute('height');

        $heightAttr = null;
        if (null !== $height && '' !== $height) {
            $heightAttr = 'auto' === $height ? 'auto' : (int) $height;
        }

        $img = \sprintf(
            '<img %s />',
            $this->htmlAttributes([
                'alt' => $this->getAttribute('alt'),
                'src' => $this->getAttribute('src'),
                'srcset' => $this->getAttribute('srcset'),
                'sizes' => $this->getAttribute('sizes'),
                'style' => 'img',
                'title' => $this->getAttribute('title'),
                'width' => $this->getContentWidth(),
                'usemap' => $this->getAttribute('usemap'),
                'height' => $heightAttr,
            ])
        );

        $href = $this->getAttribute('href');
        if (null !== $href && '' !== $href) {
            return \sprintf(
                '<a %s>%s</a>',
                $this->htmlAttributes([
                    'href' => $href,
                    'target' => $this->getAttribute('target'),
                    'rel' => $this->getAttribute('rel'),
                    'name' => $this->getAttribute('name'),
                    'title' => $this->getAttribute('title'),
                ]),
                $img
            );
        }

        return $img;
    }

    private function getContentWidth(): int
    {
        $widthAttr = $this->getAttribute('width');
        $width = null !== $widthAttr && '' !== $widthAttr ? (int) $widthAttr : \PHP_INT_MAX;

        $boxWidths = $this->getBoxWidths();

        return min($boxWidths['box'], $width);
    }

    private function addFluidMobileStyle(): void
    {
        if (null === $this->context) {
            return;
        }

        $breakpoint = $this->context->breakpoint;
        // Make the breakpoint 1px lower (e.g., 480px -> 479px)
        $lowerBreakpoint = ((int) $breakpoint - 1).'px';

        $css = \sprintf(
            '@media only screen and (max-width:%s) { table.mj-full-width-mobile { width: 100%% !important; } td.mj-full-width-mobile { width: auto !important; } }',
            $lowerBreakpoint
        );

        // Check if this style is already present
        foreach ($this->context->getComponentHeadStyles() as $style) {
            if (str_contains($style, 'mj-full-width-mobile')) {
                return;
            }
        }

        $this->context->globalData->addComponentHeadStyle($css);
    }
}
