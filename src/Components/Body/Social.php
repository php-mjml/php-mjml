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
use PhpMjml\Component\ComponentInterface;

final class Social extends BodyComponent
{
    private const INHERITABLE_ATTRIBUTES = [
        'border-radius',
        'color',
        'font-family',
        'font-size',
        'font-weight',
        'font-style',
        'icon-size',
        'icon-height',
        'icon-padding',
        'text-padding',
        'line-height',
        'text-decoration',
    ];
    protected static bool $endingTag = false;

    public static function getComponentName(): string
    {
        return 'mj-social';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'align' => 'enum(left,right,center)',
            'border-radius' => 'unit(px,%)',
            'container-background-color' => 'color',
            'color' => 'color',
            'font-family' => 'string',
            'font-size' => 'unit(px)',
            'font-style' => 'string',
            'font-weight' => 'string',
            'icon-size' => 'unit(px,%)',
            'icon-height' => 'unit(px,%)',
            'icon-padding' => 'unit(px,%){1,4}',
            'inner-padding' => 'unit(px,%){1,4}',
            'line-height' => 'unit(px,%,)',
            'mode' => 'enum(horizontal,vertical)',
            'padding-bottom' => 'unit(px,%)',
            'padding-left' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'padding-top' => 'unit(px,%)',
            'padding' => 'unit(px,%){1,4}',
            'table-layout' => 'enum(auto,fixed)',
            'text-padding' => 'unit(px,%){1,4}',
            'text-decoration' => 'string',
            'vertical-align' => 'enum(top,bottom,middle)',
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'align' => 'center',
            'border-radius' => '3px',
            'color' => '#333333',
            'font-family' => 'Ubuntu, Helvetica, Arial, sans-serif',
            'font-size' => '13px',
            'icon-size' => '20px',
            'inner-padding' => null,
            'line-height' => '22px',
            'mode' => 'horizontal',
            'padding' => '10px 25px',
            'text-decoration' => 'none',
        ];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        return [
            'tableVertical' => [
                'margin' => '0px',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getChildContext(): array
    {
        $context = parent::getChildContext();
        $context['inheritedAttributes'] = $this->getSocialElementAttributes();

        return $context;
    }

    public function render(): string
    {
        return 'horizontal' === $this->getAttribute('mode')
            ? $this->renderHorizontal()
            : $this->renderVertical();
    }

    /**
     * Get attributes to pass down to child social elements.
     *
     * @return array<string, string|null>
     */
    private function getSocialElementAttributes(): array
    {
        $base = [];

        $innerPadding = $this->getAttribute('inner-padding');
        if (null !== $innerPadding) {
            $base['padding'] = $innerPadding;
        }

        foreach (self::INHERITABLE_ATTRIBUTES as $attr) {
            $value = $this->getAttribute($attr);
            if (null !== $value) {
                $base[$attr] = $value;
            }
        }

        return $base;
    }

    private function renderHorizontal(): string
    {
        $align = $this->getAttribute('align');
        $childrenHtml = '';

        foreach ($this->children as $child) {
            if ($child instanceof BodyComponent && $child::isRawElement()) {
                $childrenHtml .= $child->render();
            } else {
                $childrenHtml .= $this->renderHorizontalChild($child, $align);
            }
        }

        return \sprintf(
            '<!--[if mso | IE]><table %s><tr><![endif]-->%s<!--[if mso | IE]></tr></table><![endif]-->',
            $this->htmlAttributes([
                'align' => $align,
                'border' => '0',
                'cellpadding' => '0',
                'cellspacing' => '0',
                'role' => 'presentation',
            ]),
            $childrenHtml,
        );
    }

    private function renderHorizontalChild(ComponentInterface $child, ?string $align): string
    {
        return \sprintf(
            '<!--[if mso | IE]><td><![endif]--><table %s><tbody>%s</tbody></table><!--[if mso | IE]></td><![endif]-->',
            $this->htmlAttributes([
                'align' => $align,
                'border' => '0',
                'cellpadding' => '0',
                'cellspacing' => '0',
                'role' => 'presentation',
                'style' => [
                    'float' => 'none',
                    'display' => 'inline-table',
                ],
            ]),
            $child->render(),
        );
    }

    private function renderVertical(): string
    {
        $childrenHtml = '';
        foreach ($this->children as $child) {
            $childrenHtml .= $child->render();
        }

        return \sprintf(
            '<table %s><tbody>%s</tbody></table>',
            $this->htmlAttributes([
                'border' => '0',
                'cellpadding' => '0',
                'cellspacing' => '0',
                'role' => 'presentation',
                'style' => 'tableVertical',
            ]),
            $childrenHtml,
        );
    }
}
