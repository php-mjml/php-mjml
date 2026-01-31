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

final class Accordion extends BodyComponent
{
    private const DEFAULT_BORDER = '2px solid black';
    private const DEFAULT_FONT_FAMILY = 'Ubuntu, Helvetica, Arial, sans-serif';
    private const DEFAULT_ICON_ALIGN = 'middle';
    private const DEFAULT_ICON_WRAPPED_URL = 'https://i.imgur.com/bIXv1bk.png';
    private const DEFAULT_ICON_WRAPPED_ALT = '+';
    private const DEFAULT_ICON_UNWRAPPED_URL = 'https://i.imgur.com/w4uTygT.png';
    private const DEFAULT_ICON_UNWRAPPED_ALT = '-';
    private const DEFAULT_ICON_POSITION = 'right';
    private const DEFAULT_ICON_HEIGHT = '32px';
    private const DEFAULT_ICON_WIDTH = '32px';
    private const DEFAULT_PADDING = '10px 25px';

    protected static bool $endingTag = false;

    public static function getComponentName(): string
    {
        return 'mj-accordion';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'container-background-color' => 'color',
            'border' => 'string',
            'font-family' => 'string',
            'icon-align' => 'enum(top,middle,bottom)',
            'icon-width' => 'unit(px,%)',
            'icon-height' => 'unit(px,%)',
            'icon-wrapped-url' => 'string',
            'icon-wrapped-alt' => 'string',
            'icon-unwrapped-url' => 'string',
            'icon-unwrapped-alt' => 'string',
            'icon-position' => 'enum(left,right)',
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
            'border' => self::DEFAULT_BORDER,
            'font-family' => self::DEFAULT_FONT_FAMILY,
            'icon-align' => self::DEFAULT_ICON_ALIGN,
            'icon-wrapped-url' => self::DEFAULT_ICON_WRAPPED_URL,
            'icon-wrapped-alt' => self::DEFAULT_ICON_WRAPPED_ALT,
            'icon-unwrapped-url' => self::DEFAULT_ICON_UNWRAPPED_URL,
            'icon-unwrapped-alt' => self::DEFAULT_ICON_UNWRAPPED_ALT,
            'icon-position' => self::DEFAULT_ICON_POSITION,
            'icon-height' => self::DEFAULT_ICON_HEIGHT,
            'icon-width' => self::DEFAULT_ICON_WIDTH,
            'padding' => self::DEFAULT_PADDING,
        ];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        return [
            'table' => [
                'width' => '100%',
                'border-collapse' => 'collapse',
                'border' => $this->getAttribute('border'),
                'border-bottom' => 'none',
                'font-family' => $this->getAttribute('font-family'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getChildContext(): array
    {
        $context = parent::getChildContext();

        $accordionData = AccordionContextResolver::resolve([
            'fontFamily' => $this->getAttribute('font-family'),
            'border' => $this->getAttribute('border'),
            'iconAlign' => $this->getAttribute('icon-align'),
            'iconWidth' => $this->getAttribute('icon-width'),
            'iconHeight' => $this->getAttribute('icon-height'),
            'iconPosition' => $this->getAttribute('icon-position'),
            'iconWrappedUrl' => $this->getAttribute('icon-wrapped-url'),
            'iconWrappedAlt' => $this->getAttribute('icon-wrapped-alt'),
            'iconUnwrappedUrl' => $this->getAttribute('icon-unwrapped-url'),
            'iconUnwrappedAlt' => $this->getAttribute('icon-unwrapped-alt'),
        ]);

        $context['componentData'][AccordionContextResolver::KEY] = $accordionData;

        return $context;
    }

    public function render(): string
    {
        // Always add head style for accordion
        $this->addHeadStyle();

        return \sprintf(
            '<table %s><tbody>%s</tbody></table>',
            $this->htmlAttributes([
                'cellspacing' => '0',
                'cellpadding' => '0',
                'class' => 'mj-accordion',
                'style' => 'table',
            ]),
            $this->renderChildren(),
        );
    }

    private function addHeadStyle(): void
    {
        if (null === $this->context) {
            return;
        }

        $css = <<<'CSS'
      noinput.mj-accordion-checkbox { display:block!important; }

      @media yahoo, only screen and (min-width:0) {
        .mj-accordion-element { display:block; }
        input.mj-accordion-checkbox, .mj-accordion-less { display:none!important; }
        input.mj-accordion-checkbox + * .mj-accordion-title { cursor:pointer; touch-action:manipulation; -webkit-user-select:none; -moz-user-select:none; user-select:none; }
        input.mj-accordion-checkbox + * .mj-accordion-content { overflow:hidden; display:none; }
        input.mj-accordion-checkbox + * .mj-accordion-more { display:block!important; }
        input.mj-accordion-checkbox:checked + * .mj-accordion-content { display:block; }
        input.mj-accordion-checkbox:checked + * .mj-accordion-more { display:none!important; }
        input.mj-accordion-checkbox:checked + * .mj-accordion-less { display:block!important; }
      }

      .moz-text-html input.mj-accordion-checkbox + * .mj-accordion-title { cursor: auto; touch-action: auto; -webkit-user-select: auto; -moz-user-select: auto; user-select: auto; }
      .moz-text-html input.mj-accordion-checkbox + * .mj-accordion-content { overflow: hidden; display: block; }
      .moz-text-html input.mj-accordion-checkbox + * .mj-accordion-ico { display: none; }

      @goodbye { @gmail }
CSS;

        $this->context->globalData->addHeadStyle('mj-accordion', $css);
    }
}
