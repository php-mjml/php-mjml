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

final class Text extends BodyComponent
{
    protected static bool $endingTag = true;

    public static function getComponentName(): string
    {
        return 'mj-text';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'align' => 'enum(left,right,center,justify)',
            'background-color' => 'color',
            'color' => 'color',
            'container-background-color' => 'color',
            'font-family' => 'string',
            'font-size' => 'unit(px)',
            'font-style' => 'string',
            'font-weight' => 'string',
            'height' => 'unit(px,%)',
            'letter-spacing' => 'unitWithNegative(px,em)',
            'line-height' => 'unit(px,%,)',
            'padding-bottom' => 'unit(px,%)',
            'padding-left' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'padding-top' => 'unit(px,%)',
            'padding' => 'unit(px,%){1,4}',
            'text-decoration' => 'string',
            'text-transform' => 'string',
            'vertical-align' => 'enum(top,bottom,middle)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'align' => 'left',
            'color' => '#000000',
            'font-family' => 'Ubuntu, Helvetica, Arial, sans-serif',
            'font-size' => '13px',
            'line-height' => '1',
            'padding' => '10px 25px',
        ];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        return [
            'text' => [
                'font-family' => $this->getAttribute('font-family'),
                'font-size' => $this->getAttribute('font-size'),
                'font-style' => $this->getAttribute('font-style'),
                'font-weight' => $this->getAttribute('font-weight'),
                'letter-spacing' => $this->getAttribute('letter-spacing'),
                'line-height' => $this->getAttribute('line-height'),
                'text-align' => $this->getAttribute('align'),
                'text-decoration' => $this->getAttribute('text-decoration'),
                'text-transform' => $this->getAttribute('text-transform'),
                'color' => $this->getAttribute('color'),
                'height' => $this->getAttribute('height'),
            ],
        ];
    }

    public function render(): string
    {
        $height = $this->getAttribute('height');

        if (null !== $height && '' !== $height) {
            $tableHtml = \sprintf(
                '<table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td height="%s" style="vertical-align:top;height:%s;">',
                htmlspecialchars((string) $height, \ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $height, \ENT_QUOTES, 'UTF-8'),
            );
            $tableEndHtml = '</td></tr></table>';

            return ConditionalTag::wrap($tableHtml)
                .$this->renderContent()
                .ConditionalTag::wrap($tableEndHtml);
        }

        return $this->renderContent();
    }

    private function renderContent(): string
    {
        return \sprintf(
            '<div %s>%s</div>',
            $this->htmlAttributes(['style' => 'text']),
            $this->getContent(),
        );
    }
}
