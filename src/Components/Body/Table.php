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

final class Table extends BodyComponent
{
    protected static bool $endingTag = true;

    public static function getComponentName(): string
    {
        return 'mj-table';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'align' => 'enum(left,right,center)',
            'border' => 'string',
            'cellpadding' => 'integer',
            'cellspacing' => 'integer',
            'container-background-color' => 'color',
            'color' => 'color',
            'font-family' => 'string',
            'font-size' => 'unit(px)',
            'font-weight' => 'string',
            'line-height' => 'unit(px,%,)',
            'padding-bottom' => 'unit(px,%)',
            'padding-left' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'padding-top' => 'unit(px,%)',
            'padding' => 'unit(px,%){1,4}',
            'role' => 'enum(none,presentation)',
            'table-layout' => 'enum(auto,fixed,initial,inherit)',
            'vertical-align' => 'enum(top,bottom,middle)',
            'width' => 'unit(px,%,auto)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'align' => 'left',
            'border' => 'none',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'color' => '#000000',
            'font-family' => 'Ubuntu, Helvetica, Arial, sans-serif',
            'font-size' => '13px',
            'line-height' => '22px',
            'padding' => '10px 25px',
            'table-layout' => 'auto',
            'width' => '100%',
        ];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        $styles = [
            'color' => $this->getAttribute('color'),
            'font-family' => $this->getAttribute('font-family'),
            'font-size' => $this->getAttribute('font-size'),
            'line-height' => $this->getAttribute('line-height'),
            'table-layout' => $this->getAttribute('table-layout'),
            'width' => $this->getAttribute('width'),
            'border' => $this->getAttribute('border'),
        ];

        if ($this->hasCellspacing()) {
            $styles['border-collapse'] = 'separate';
        }

        return [
            'table' => $styles,
        ];
    }

    public function render(): string
    {
        return \sprintf(
            '<table %s>%s</table>',
            $this->htmlAttributes([
                'cellpadding' => $this->getAttribute('cellpadding'),
                'cellspacing' => $this->getAttribute('cellspacing'),
                'role' => $this->getAttribute('role'),
                'width' => $this->getWidth(),
                'border' => '0',
                'style' => 'table',
            ]),
            $this->getContent(),
        );
    }

    private function getWidth(): string
    {
        $width = $this->getAttribute('width');

        if (null === $width) {
            return '';
        }

        if ('auto' === $width) {
            return $width;
        }

        $parsed = WidthParser::parse($width);

        return '%' === $parsed['unit'] ? $width : (string) $parsed['parsedWidth'];
    }

    private function hasCellspacing(): bool
    {
        $cellspacing = $this->getAttribute('cellspacing');

        if (null === $cellspacing) {
            return false;
        }

        $numericValue = (float) preg_replace('/[^\d.]/', '', (string) $cellspacing);

        return !is_nan($numericValue) && $numericValue > 0;
    }
}
