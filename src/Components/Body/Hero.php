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
use PhpMjml\Helper\ConditionalTag;

final class Hero extends BodyComponent
{
    protected static bool $endingTag = false;

    public static function getComponentName(): string
    {
        return 'mj-hero';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'mode' => 'string',
            'height' => 'unit(px,%)',
            'background-url' => 'string',
            'background-width' => 'unit(px,%)',
            'background-height' => 'unit(px,%)',
            'background-position' => 'string',
            'border-radius' => 'string',
            'container-background-color' => 'color',
            'inner-background-color' => 'color',
            'inner-padding' => 'unit(px,%){1,4}',
            'inner-padding-top' => 'unit(px,%)',
            'inner-padding-left' => 'unit(px,%)',
            'inner-padding-right' => 'unit(px,%)',
            'inner-padding-bottom' => 'unit(px,%)',
            'padding' => 'unit(px,%){1,4}',
            'padding-bottom' => 'unit(px,%)',
            'padding-left' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'padding-top' => 'unit(px,%)',
            'background-color' => 'color',
            'vertical-align' => 'enum(top,bottom,middle)',
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'mode' => 'fixed-height',
            'height' => '0px',
            'background-url' => null,
            'background-position' => 'center center',
            'padding' => '0px',
            'padding-bottom' => null,
            'padding-left' => null,
            'padding-right' => null,
            'padding-top' => null,
            'background-color' => '#ffffff',
            'vertical-align' => 'top',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getChildContext(): array
    {
        $context = $this->context?->toArray() ?? [];
        $containerWidth = $this->context->containerWidth ?? 600;

        $paddingLeft = $this->getShorthandAttrValue('padding', 'left');
        $paddingRight = $this->getShorthandAttrValue('padding', 'right');
        $paddingSize = $paddingLeft + $paddingRight;

        $currentContainerWidth = $containerWidth - $paddingSize;

        $context['containerWidth'] = $currentContainerWidth;

        return $context;
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        $containerWidth = $this->context->containerWidth ?? 600;

        $backgroundHeight = $this->getAttribute('background-height');
        $backgroundWidth = $this->getAttribute('background-width');

        $backgroundRatio = 0;
        if (null !== $backgroundHeight && null !== $backgroundWidth) {
            $heightVal = (int) $backgroundHeight;
            $widthVal = (int) $backgroundWidth;
            if ($widthVal > 0) {
                $backgroundRatio = (int) round(($heightVal / $widthVal) * 100);
            }
        }

        $width = $backgroundWidth ?? "{$containerWidth}px";

        return [
            'div' => [
                'margin' => '0 auto',
                'max-width' => "{$containerWidth}px",
            ],
            'table' => [
                'width' => '100%',
            ],
            'tr' => [
                'vertical-align' => 'top',
            ],
            'td-fluid' => [
                'width' => '0.01%',
                'padding-bottom' => "{$backgroundRatio}%",
                'mso-padding-bottom-alt' => '0',
            ],
            'outlook-table' => [
                'width' => "{$containerWidth}px",
            ],
            'outlook-td' => [
                'line-height' => '0',
                'font-size' => '0',
                'mso-line-height-rule' => 'exactly',
            ],
            'outlook-inner-table' => [
                'width' => "{$containerWidth}px",
            ],
            'outlook-image' => [
                'border' => '0',
                'height' => $this->getAttribute('background-height'),
                'mso-position-horizontal' => 'center',
                'position' => 'absolute',
                'top' => '0',
                'width' => $width,
                'z-index' => '-3',
            ],
            'outlook-inner-td' => [
                'background-color' => $this->getAttribute('inner-background-color'),
                'padding' => $this->getAttribute('inner-padding'),
                'padding-top' => $this->getAttribute('inner-padding-top'),
                'padding-left' => $this->getAttribute('inner-padding-left'),
                'padding-right' => $this->getAttribute('inner-padding-right'),
                'padding-bottom' => $this->getAttribute('inner-padding-bottom'),
            ],
            'inner-table' => [
                'width' => '100%',
                'margin' => '0px',
            ],
            'inner-div' => [
                'background-color' => $this->getAttribute('inner-background-color'),
                'float' => $this->getAttribute('align'),
                'margin' => '0px auto',
                'width' => $this->getAttribute('width'),
            ],
            'inner-td' => [],
        ];
    }

    public function render(): string
    {
        $containerWidth = $this->context->containerWidth ?? 600;

        $outlookTableAttributes = [
            'align' => 'center',
            'border' => '0',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'role' => 'presentation',
            'style' => 'outlook-table',
            'width' => $containerWidth,
        ];

        $outlookImageAttributes = [
            'style' => 'outlook-image',
            'src' => $this->getAttribute('background-url'),
            'xmlns:v' => 'urn:schemas-microsoft-com:vml',
        ];

        $divAttributes = [
            'align' => $this->getAttribute('align'),
            'class' => $this->getAttribute('css-class'),
            'style' => 'div',
        ];

        $tableAttributes = [
            'border' => '0',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'role' => 'presentation',
            'style' => 'table',
        ];

        $trAttributes = [
            'style' => 'tr',
        ];

        $outlookStart = ConditionalTag::START_CONDITIONAL
            .\sprintf(
                '<table %s><tr><td %s><v:image %s />',
                $this->htmlAttributes($outlookTableAttributes),
                $this->htmlAttributes(['style' => 'outlook-td']),
                $this->htmlAttributes($outlookImageAttributes),
            )
            .ConditionalTag::END_CONDITIONAL;

        $outlookEnd = ConditionalTag::START_CONDITIONAL
            .'</td></tr></table>'
            .ConditionalTag::END_CONDITIONAL;

        return $outlookStart
            .\sprintf(
                '<div %s><table %s><tbody><tr %s>%s</tr></tbody></table></div>',
                $this->htmlAttributes($divAttributes),
                $this->htmlAttributes($tableAttributes),
                $this->htmlAttributes($trAttributes),
                $this->renderMode(),
            )
            .$outlookEnd;
    }

    private function getBackground(): string
    {
        $parts = [$this->getAttribute('background-color')];

        $backgroundUrl = $this->getAttribute('background-url');
        if (null !== $backgroundUrl) {
            $parts[] = \sprintf("url('%s')", $backgroundUrl);
            $parts[] = 'no-repeat';
            $parts[] = \sprintf('%s / cover', $this->getAttribute('background-position'));
        }

        $parts = array_filter($parts, fn ($v) => null !== $v && '' !== $v);

        return implode(' ', $parts);
    }

    private function renderContent(): string
    {
        $containerWidth = $this->context->containerWidth ?? 600;

        $outlookInnerTableAttributes = [
            'align' => $this->getAttribute('align'),
            'border' => '0',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'style' => 'outlook-inner-table',
            'width' => (int) preg_replace('/px$/', '', (string) $containerWidth),
        ];

        $innerDivAttributes = [
            'align' => $this->getAttribute('align'),
            'class' => 'mj-hero-content',
            'style' => 'inner-div',
        ];

        $innerTableAttributes = [
            'border' => '0',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'role' => 'presentation',
            'style' => 'inner-table',
        ];

        $outlookStart = ConditionalTag::START_CONDITIONAL
            .\sprintf(
                '<table %s><tr><td %s>',
                $this->htmlAttributes($outlookInnerTableAttributes),
                $this->htmlAttributes(['style' => 'outlook-inner-td']),
            )
            .ConditionalTag::END_CONDITIONAL;

        $outlookEnd = ConditionalTag::START_CONDITIONAL
            .'</td></tr></table>'
            .ConditionalTag::END_CONDITIONAL;

        $childrenHtml = $this->renderHeroChildren();

        return $outlookStart
            .\sprintf(
                '<div %s><table %s><tbody><tr><td %s><table %s><tbody>%s</tbody></table></td></tr></tbody></table></div>',
                $this->htmlAttributes($innerDivAttributes),
                $this->htmlAttributes($innerTableAttributes),
                $this->htmlAttributes(['style' => 'inner-td']),
                $this->htmlAttributes($innerTableAttributes),
                $childrenHtml,
            )
            .$outlookEnd;
    }

    private function renderHeroChildren(): string
    {
        $output = '';

        foreach ($this->children as $child) {
            if ($child instanceof BodyComponent && $child::isRawElement()) {
                $output .= $child->render();
            } elseif ($child instanceof ComponentInterface) {
                $output .= $this->renderHeroChild($child);
            }
        }

        return $output;
    }

    private function renderHeroChild(ComponentInterface $child): string
    {
        $tdStyle = [
            'background' => $child->getAttribute('container-background-color'),
            'font-size' => '0px',
            'padding' => $child->getAttribute('padding'),
            'padding-top' => $child->getAttribute('padding-top'),
            'padding-right' => $child->getAttribute('padding-right'),
            'padding-bottom' => $child->getAttribute('padding-bottom'),
            'padding-left' => $child->getAttribute('padding-left'),
            'word-break' => 'break-word',
        ];

        $tdAttributes = [
            'align' => $child->getAttribute('align'),
            'background' => $child->getAttribute('container-background-color'),
            'class' => $child->getAttribute('css-class'),
            'style' => $tdStyle,
        ];

        return \sprintf(
            '<tr><td %s>%s</td></tr>',
            $this->htmlAttributes($tdAttributes),
            $child->render(),
        );
    }

    private function renderMode(): string
    {
        $mode = $this->getAttribute('mode');

        $commonStyle = [
            'background' => $this->getBackground(),
            'background-position' => $this->getAttribute('background-position'),
            'background-repeat' => 'no-repeat',
            'border-radius' => $this->getAttribute('border-radius'),
            'padding' => $this->getAttribute('padding'),
            'padding-top' => $this->getAttribute('padding-top'),
            'padding-left' => $this->getAttribute('padding-left'),
            'padding-right' => $this->getAttribute('padding-right'),
            'padding-bottom' => $this->getAttribute('padding-bottom'),
            'vertical-align' => $this->getAttribute('vertical-align'),
        ];

        $commonAttributes = [
            'background' => $this->getAttribute('background-url'),
            'style' => $commonStyle,
        ];

        if ('fluid-height' === $mode) {
            $magicTdAttributes = ['style' => 'td-fluid'];

            return \sprintf(
                '<td %s /><td %s>%s</td><td %s />',
                $this->htmlAttributes($magicTdAttributes),
                $this->htmlAttributes($commonAttributes),
                $this->renderContent(),
                $this->htmlAttributes($magicTdAttributes),
            );
        }

        // Default: fixed-height mode
        $height = (int) $this->getAttribute('height')
            - $this->getShorthandAttrValue('padding', 'top')
            - $this->getShorthandAttrValue('padding', 'bottom');

        $fixedHeightStyle = array_merge($commonStyle, [
            'height' => "{$height}px",
        ]);

        $fixedHeightAttributes = [
            'background' => $this->getAttribute('background-url'),
            'style' => $fixedHeightStyle,
            'height' => $height,
        ];

        return \sprintf(
            '<td %s>%s</td>',
            $this->htmlAttributes($fixedHeightAttributes),
            $this->renderContent(),
        );
    }
}
