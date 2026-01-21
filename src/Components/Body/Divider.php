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

final class Divider extends BodyComponent
{
    protected static bool $endingTag = true;

    public static function getComponentName(): string
    {
        return 'mj-divider';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'align' => 'enum(left,center,right)',
            'border-color' => 'color',
            'border-style' => 'string',
            'border-width' => 'unit(px)',
            'container-background-color' => 'color',
            'padding' => 'unit(px,%){1,4}',
            'padding-bottom' => 'unit(px,%)',
            'padding-left' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'padding-top' => 'unit(px,%)',
            'width' => 'unit(px,%)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'align' => 'center',
            'border-color' => '#000000',
            'border-style' => 'solid',
            'border-width' => '4px',
            'padding' => '10px 25px',
            'width' => '100%',
        ];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        $margin = $this->computeAlign();
        $borderTop = \sprintf(
            '%s %s %s',
            $this->getAttribute('border-style'),
            $this->getAttribute('border-width'),
            $this->getAttribute('border-color')
        );

        return [
            'p' => [
                'border-top' => $borderTop,
                'font-size' => '1px',
                'margin' => $margin,
                'width' => $this->getAttribute('width'),
            ],
            'outlook' => [
                'border-top' => $borderTop,
                'font-size' => '1px',
                'margin' => $margin,
                'width' => $this->getOutlookWidth(),
            ],
        ];
    }

    public function render(): string
    {
        return \sprintf(
            '<p %s></p>%s',
            $this->htmlAttributes(['style' => 'p']),
            $this->renderAfter()
        );
    }

    private function computeAlign(): string
    {
        $align = $this->getAttribute('align');

        if ('left' === $align) {
            return '0px';
        }

        if ('right' === $align) {
            return '0px 0px 0px auto';
        }

        return '0px auto';
    }

    private function getOutlookWidth(): string
    {
        $containerWidth = (null !== $this->context) ? $this->context->containerWidth : 600;
        $paddingSize = $this->getShorthandAttrValue('padding', 'left')
            + $this->getShorthandAttrValue('padding', 'right');

        $width = $this->getAttribute('width');

        if (null === $width) {
            return \sprintf('%dpx', (int) $containerWidth - $paddingSize);
        }

        $parsed = WidthParser::parse($width);
        $parsedWidth = $parsed['parsedWidth'];
        $unit = $parsed['unit'];

        if ('%' === $unit) {
            $effectiveWidth = (int) $containerWidth - $paddingSize;
            $percentMultiplier = (int) $parsedWidth / 100;

            return \sprintf('%dpx', (int) ($effectiveWidth * $percentMultiplier));
        }

        if ('px' === $unit) {
            return $width;
        }

        return \sprintf('%dpx', (int) $containerWidth - $paddingSize);
    }

    private function renderAfter(): string
    {
        $outlookWidth = $this->getOutlookWidth();

        return \sprintf(
            '
      <!--[if mso | IE]>
        <table %s>
          <tr>
            <td style="height:0;line-height:0;">
              &nbsp;
            </td>
          </tr>
        </table>
      <![endif]-->',
            $this->htmlAttributes([
                'align' => $this->getAttribute('align'),
                'border' => '0',
                'cellpadding' => '0',
                'cellspacing' => '0',
                'style' => 'outlook',
                'role' => 'presentation',
                'width' => $outlookWidth,
            ])
        );
    }
}
