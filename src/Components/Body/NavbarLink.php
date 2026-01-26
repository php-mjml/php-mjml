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

final class NavbarLink extends BodyComponent
{
    private const DEFAULT_COLOR = '#000000';
    private const DEFAULT_FONT_FAMILY = 'Ubuntu, Helvetica, Arial, sans-serif';
    private const DEFAULT_FONT_SIZE = '13px';
    private const DEFAULT_FONT_WEIGHT = 'normal';
    private const DEFAULT_LINE_HEIGHT = '22px';
    private const DEFAULT_PADDING = '15px 10px';
    private const DEFAULT_TARGET = '_blank';
    private const DEFAULT_TEXT_DECORATION = 'none';
    private const DEFAULT_TEXT_TRANSFORM = 'uppercase';

    protected static bool $endingTag = true;

    public static function getComponentName(): string
    {
        return 'mj-navbar-link';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'color' => 'color',
            'font-family' => 'string',
            'font-size' => 'unit(px)',
            'font-style' => 'string',
            'font-weight' => 'string',
            'href' => 'string',
            'name' => 'string',
            'target' => 'string',
            'rel' => 'string',
            'letter-spacing' => 'unitWithNegative(px,em)',
            'line-height' => 'unit(px,%,)',
            'padding-bottom' => 'unit(px,%)',
            'padding-left' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'padding-top' => 'unit(px,%)',
            'padding' => 'unit(px,%){1,4}',
            'text-decoration' => 'string',
            'text-transform' => 'string',
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'color' => self::DEFAULT_COLOR,
            'font-family' => self::DEFAULT_FONT_FAMILY,
            'font-size' => self::DEFAULT_FONT_SIZE,
            'font-weight' => self::DEFAULT_FONT_WEIGHT,
            'line-height' => self::DEFAULT_LINE_HEIGHT,
            'padding' => self::DEFAULT_PADDING,
            'target' => self::DEFAULT_TARGET,
            'text-decoration' => self::DEFAULT_TEXT_DECORATION,
            'text-transform' => self::DEFAULT_TEXT_TRANSFORM,
        ];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        return [
            'a' => [
                'display' => 'inline-block',
                'color' => $this->getAttribute('color'),
                'font-family' => $this->getAttribute('font-family'),
                'font-size' => $this->getAttribute('font-size'),
                'font-style' => $this->getAttribute('font-style'),
                'font-weight' => $this->getAttribute('font-weight'),
                'letter-spacing' => $this->getAttribute('letter-spacing'),
                'line-height' => $this->getAttribute('line-height'),
                'text-decoration' => $this->getAttribute('text-decoration'),
                'text-transform' => $this->getAttribute('text-transform'),
                'padding' => $this->getAttribute('padding'),
                'padding-top' => $this->getAttribute('padding-top'),
                'padding-left' => $this->getAttribute('padding-left'),
                'padding-right' => $this->getAttribute('padding-right'),
                'padding-bottom' => $this->getAttribute('padding-bottom'),
            ],
            'td' => [
                'padding' => $this->getAttribute('padding'),
                'padding-top' => $this->getAttribute('padding-top'),
                'padding-left' => $this->getAttribute('padding-left'),
                'padding-right' => $this->getAttribute('padding-right'),
                'padding-bottom' => $this->getAttribute('padding-bottom'),
            ],
        ];
    }

    public function render(): string
    {
        $tdHtml = ConditionalTag::wrap(\sprintf(
            '<td %s>',
            $this->htmlAttributes([
                'style' => 'td',
                'class' => $this->suffixCssClasses($this->getAttribute('css-class'), 'outlook'),
            ])
        ));

        $closeTdHtml = ConditionalTag::wrap('</td>');

        return $tdHtml.$this->renderContent().$closeTdHtml;
    }

    private function renderContent(): string
    {
        $href = $this->getAttribute('href');
        $navbarBaseUrl = $this->getNavbarBaseUrl();

        $link = (null !== $navbarBaseUrl && null !== $href)
            ? $navbarBaseUrl.$href
            : $href;

        $cssClass = $this->getAttribute('css-class');
        $classAttr = null !== $cssClass ? 'mj-link '.$cssClass : 'mj-link';

        // Add spaces around content to match JS template literal formatting
        return \sprintf(
            '<a %s> %s </a>',
            $this->htmlAttributes([
                'class' => $classAttr,
                'href' => $link,
                'rel' => $this->getAttribute('rel'),
                'target' => $this->getAttribute('target'),
                'name' => $this->getAttribute('name'),
                'style' => 'a',
            ]),
            $this->getContent(),
        );
    }

    private function getNavbarBaseUrl(): ?string
    {
        if (null === $this->context) {
            return null;
        }

        $contextArray = $this->context->toArray();

        return $contextArray['navbarBaseUrl'] ?? null;
    }

    private function suffixCssClasses(?string $classes, string $suffix): string
    {
        if (null === $classes || '' === $classes) {
            return '';
        }

        $classArray = preg_split('/\s+/', $classes, -1, \PREG_SPLIT_NO_EMPTY);
        if (false === $classArray || [] === $classArray) {
            return '';
        }

        return implode(' ', array_map(
            fn (string $class) => "{$class}-{$suffix}",
            $classArray
        ));
    }
}
