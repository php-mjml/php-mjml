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
use PhpMjml\Component\Context\NavbarContextResolver;
use PhpMjml\Helper\ConditionalTag;

final class Navbar extends BodyComponent
{
    private const HAMBURGER_MODE = 'hamburger';
    private const DEFAULT_ICO_OPEN = '&#9776;';
    private const DEFAULT_ICO_CLOSE = '&#8855;';
    private const DEFAULT_ICO_COLOR = '#000000';
    private const DEFAULT_ICO_FONT_SIZE = '30px';
    private const DEFAULT_ICO_FONT_FAMILY = 'Ubuntu, Helvetica, Arial, sans-serif';
    private const DEFAULT_ICO_TEXT_TRANSFORM = 'uppercase';
    private const DEFAULT_ICO_PADDING = '10px';
    private const DEFAULT_ICO_TEXT_DECORATION = 'none';
    private const DEFAULT_ICO_LINE_HEIGHT = '30px';

    protected static bool $endingTag = false;

    public static function getComponentName(): string
    {
        return 'mj-navbar';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'align' => 'enum(left,center,right)',
            'base-url' => 'string',
            'hamburger' => 'string',
            'ico-align' => 'enum(left,center,right)',
            'ico-open' => 'string',
            'ico-close' => 'string',
            'ico-color' => 'color',
            'ico-font-size' => 'unit(px,%)',
            'ico-font-family' => 'string',
            'ico-text-transform' => 'string',
            'ico-padding' => 'unit(px,%){1,4}',
            'ico-padding-left' => 'unit(px,%)',
            'ico-padding-top' => 'unit(px,%)',
            'ico-padding-right' => 'unit(px,%)',
            'ico-padding-bottom' => 'unit(px,%)',
            'padding' => 'unit(px,%){1,4}',
            'padding-left' => 'unit(px,%)',
            'padding-top' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'padding-bottom' => 'unit(px,%)',
            'ico-text-decoration' => 'string',
            'ico-line-height' => 'unit(px,%,)',
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'align' => 'center',
            'base-url' => null,
            'hamburger' => null,
            'ico-align' => 'center',
            'ico-open' => self::DEFAULT_ICO_OPEN,
            'ico-close' => self::DEFAULT_ICO_CLOSE,
            'ico-color' => self::DEFAULT_ICO_COLOR,
            'ico-font-size' => self::DEFAULT_ICO_FONT_SIZE,
            'ico-font-family' => self::DEFAULT_ICO_FONT_FAMILY,
            'ico-text-transform' => self::DEFAULT_ICO_TEXT_TRANSFORM,
            'ico-padding' => self::DEFAULT_ICO_PADDING,
            'ico-text-decoration' => self::DEFAULT_ICO_TEXT_DECORATION,
            'ico-line-height' => self::DEFAULT_ICO_LINE_HEIGHT,
        ];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        return [
            'div' => [
                'align' => $this->getAttribute('align'),
                'width' => '100%',
            ],
            'label' => [
                'display' => 'block',
                'cursor' => 'pointer',
                'mso-hide' => 'all',
                '-moz-user-select' => 'none',
                'user-select' => 'none',
                'color' => $this->getAttribute('ico-color'),
                'font-size' => $this->getAttribute('ico-font-size'),
                'font-family' => $this->getAttribute('ico-font-family'),
                'text-transform' => $this->getAttribute('ico-text-transform'),
                'text-decoration' => $this->getAttribute('ico-text-decoration'),
                'line-height' => $this->getAttribute('ico-line-height'),
                'padding' => $this->getAttribute('ico-padding'),
                'padding-top' => $this->getAttribute('ico-padding-top'),
                'padding-right' => $this->getAttribute('ico-padding-right'),
                'padding-bottom' => $this->getAttribute('ico-padding-bottom'),
                'padding-left' => $this->getAttribute('ico-padding-left'),
            ],
            'trigger' => [
                'display' => 'none',
                'max-height' => '0px',
                'max-width' => '0px',
                'font-size' => '0px',
                'overflow' => 'hidden',
            ],
            'icoOpen' => [
                'mso-hide' => 'all',
            ],
            'icoClose' => [
                'display' => 'none',
                'mso-hide' => 'all',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getChildContext(): array
    {
        $context = parent::getChildContext();

        $navbarData = NavbarContextResolver::resolve([
            'baseUrl' => $this->getAttribute('base-url'),
        ]);

        $context['componentData'][NavbarContextResolver::KEY] = $navbarData;

        return $context;
    }

    public function render(): string
    {
        // Always add head style for hamburger menu (matches JS behavior where headStyle is always defined)
        $this->addHeadStyle();

        $hamburgerHtml = self::HAMBURGER_MODE === $this->getAttribute('hamburger')
            ? $this->renderHamburger()
            : '';

        $align = $this->getAttribute('align');

        // Note: The JS uses `style: this.htmlAttributes('div')` which is a quirk that results
        // in an empty `style` attribute. We match this behavior by not applying div styles.
        return \sprintf(
            '%s<div %s>%s%s%s</div>',
            $hamburgerHtml,
            $this->htmlAttributes([
                'class' => 'mj-inline-links',
                'style' => [],
            ]),
            ConditionalTag::wrap(\sprintf(
                '<table role="presentation" border="0" cellpadding="0" cellspacing="0" align="%s"><tr>',
                $align
            )),
            $this->renderChildren(),
            ConditionalTag::wrap('</tr></table>'),
        );
    }

    private function addHeadStyle(): void
    {
        if (null === $this->context) {
            return;
        }

        $breakpoint = $this->context->breakpoint;
        $lowerBreakpoint = $this->makeLowerBreakpoint($breakpoint);

        $css = <<<CSS
      noinput.mj-menu-checkbox { display:block!important; max-height:none!important; visibility:visible!important; }

      @media only screen and (max-width:{$lowerBreakpoint}) {
        .mj-menu-checkbox[type="checkbox"] ~ .mj-inline-links { display:none!important; }
        .mj-menu-checkbox[type="checkbox"]:checked ~ .mj-inline-links,
        .mj-menu-checkbox[type="checkbox"] ~ .mj-menu-trigger { display:block!important; max-width:none!important; max-height:none!important; font-size:inherit!important; }
        .mj-menu-checkbox[type="checkbox"] ~ .mj-inline-links > a { display:block!important; }
        .mj-menu-checkbox[type="checkbox"]:checked ~ .mj-menu-trigger .mj-menu-icon-close { display:block!important; }
        .mj-menu-checkbox[type="checkbox"]:checked ~ .mj-menu-trigger .mj-menu-icon-open { display:none!important; }
      }
CSS;

        $this->context->globalData->addHeadStyle('mj-navbar', $css);
    }

    private function makeLowerBreakpoint(string $breakpoint): string
    {
        if (preg_match('/(\d+)/', $breakpoint, $matches)) {
            $pixels = (int) $matches[1] - 1;

            return $pixels.'px';
        }

        return $breakpoint;
    }

    private function renderHamburger(): string
    {
        $labelKey = $this->generateRandomHexString(16);

        $checkboxHtml = ConditionalTag::wrapMso(
            \sprintf(
                '<input type="checkbox" id="%s" class="mj-menu-checkbox" style="display:none !important; max-height:0; visibility:hidden;" />',
                $labelKey
            ),
            true
        );

        return \sprintf(
            '%s<div %s><label %s><span %s>%s</span><span %s>%s</span></label></div>',
            $checkboxHtml,
            $this->htmlAttributes([
                'class' => 'mj-menu-trigger',
                'style' => 'trigger',
            ]),
            $this->htmlAttributes([
                'for' => $labelKey,
                'class' => 'mj-menu-label',
                'style' => 'label',
                'align' => $this->getAttribute('ico-align'),
            ]),
            $this->htmlAttributes([
                'class' => 'mj-menu-icon-open',
                'style' => 'icoOpen',
            ]),
            $this->getAttribute('ico-open'),
            $this->htmlAttributes([
                'class' => 'mj-menu-icon-close',
                'style' => 'icoClose',
            ]),
            $this->getAttribute('ico-close'),
        );
    }

    private function generateRandomHexString(int $length): string
    {
        $byteLength = max(1, (int) ($length / 2));

        return bin2hex(random_bytes($byteLength));
    }
}
