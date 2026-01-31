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
use PhpMjml\Component\Context\GapContextResolver;
use PhpMjml\Helper\ConditionalTag;
use PhpMjml\Helper\CssHelper;

final class Section extends BodyComponent
{
    public static function getComponentName(): string
    {
        return 'mj-section';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'background-color' => 'color',
            'background-url' => 'string',
            'background-repeat' => 'enum(repeat,no-repeat)',
            'background-size' => 'string',
            'background-position' => 'string',
            'background-position-x' => 'string',
            'background-position-y' => 'string',
            'border' => 'string',
            'border-bottom' => 'string',
            'border-left' => 'string',
            'border-radius' => 'string',
            'border-right' => 'string',
            'border-top' => 'string',
            'direction' => 'enum(ltr,rtl)',
            'full-width' => 'enum(full-width,false,)',
            'padding' => 'unit(px,%){1,4}',
            'padding-top' => 'unit(px,%)',
            'padding-bottom' => 'unit(px,%)',
            'padding-left' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'text-align' => 'enum(left,center,right)',
            'text-padding' => 'unit(px,%){1,4}',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'background-repeat' => 'repeat',
            'background-size' => 'auto',
            'background-position' => 'top center',
            'direction' => 'ltr',
            'padding' => '20px 0',
            'text-align' => 'center',
            'text-padding' => '4px 4px 4px 0',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getChildContext(): array
    {
        $context = $this->context?->toArray() ?? [];
        $boxWidths = $this->getBoxWidths();

        $context['containerWidth'] = $boxWidths['box'];

        // Propagate gap to children via componentData
        $gap = $this->getAttribute('gap');
        if (null !== $gap) {
            $context['componentData'][GapContextResolver::KEY] = GapContextResolver::resolve(['value' => $gap]);
        }

        return $context;
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        $containerWidth = $this->context->containerWidth ?? 600;
        $fullWidth = $this->isFullWidth();
        $hasBorderRadius = $this->hasBorderRadius();

        $background = null !== $this->getAttribute('background-url')
            ? [
                'background' => $this->getBackground(),
                'background-position' => $this->getBackgroundString(),
                'background-repeat' => $this->getAttribute('background-repeat'),
                'background-size' => $this->getAttribute('background-size'),
            ]
            : [
                'background' => $this->getAttribute('background-color'),
                'background-color' => $this->getAttribute('background-color'),
            ];

        return [
            'tableFullwidth' => array_merge(
                $fullWidth ? $background : [],
                ['width' => '100%'],
            ),
            'table' => array_merge(
                $fullWidth ? [] : $background,
                [
                    'width' => '100%',
                ],
                $hasBorderRadius ? ['border-collapse' => 'separate'] : [],
            ),
            'td' => [
                'border' => $this->getAttribute('border'),
                'border-bottom' => $this->getAttribute('border-bottom'),
                'border-left' => $this->getAttribute('border-left'),
                'border-right' => $this->getAttribute('border-right'),
                'border-top' => $this->getAttribute('border-top'),
                'border-radius' => $this->getAttribute('border-radius'),
                'direction' => $this->getAttribute('direction'),
                'font-size' => '0px',
                'padding' => $this->getAttribute('padding'),
                'padding-bottom' => $this->getAttribute('padding-bottom'),
                'padding-left' => $this->getAttribute('padding-left'),
                'padding-right' => $this->getAttribute('padding-right'),
                'padding-top' => $this->getAttribute('padding-top'),
                'text-align' => $this->getAttribute('text-align'),
            ],
            'div' => array_merge(
                $fullWidth ? [] : $background,
                [
                    'margin' => '0px auto',
                    'max-width' => "{$containerWidth}px",
                    'border-radius' => $this->getAttribute('border-radius'),
                ],
                $hasBorderRadius ? ['overflow' => 'hidden'] : [],
                !$this->isFirstSection() && $this->hasGap() ? ['margin-top' => $this->getGap()] : [],
            ),
            'innerDiv' => [
                'line-height' => '0',
                'font-size' => '0',
            ],
        ];
    }

    public function render(): string
    {
        return $this->isFullWidth() ? $this->renderFullWidth() : $this->renderSimple();
    }

    /**
     * Check if this section has a gap defined in its context (from parent wrapper).
     */
    private function hasGap(): bool
    {
        $gap = $this->getGap();

        return null !== $gap && '' !== $gap;
    }

    /**
     * Get the gap value from context.
     */
    private function getGap(): ?string
    {
        $gapData = $this->context?->getComponentData(GapContextResolver::KEY);

        return $gapData['value'] ?? null;
    }

    /**
     * Check if this is the first section (index 0).
     */
    private function isFirstSection(): bool
    {
        return 0 === ($this->props['index'] ?? 0);
    }

    private function getBackground(): string
    {
        $parts = array_filter([
            $this->getAttribute('background-color'),
            $this->hasBackground()
                ? \sprintf("url('%s')", $this->getAttribute('background-url'))
                : null,
            $this->hasBackground() ? $this->getBackgroundString() : null,
            $this->hasBackground() ? \sprintf('/ %s', $this->getAttribute('background-size')) : null,
            $this->hasBackground() ? $this->getAttribute('background-repeat') : null,
        ], static fn ($v) => null !== $v && '' !== $v);

        return implode(' ', $parts);
    }

    private function getBackgroundString(): string
    {
        $pos = $this->getBackgroundPosition();

        return "{$pos['posX']} {$pos['posY']}";
    }

    /**
     * @return array{posX: string, posY: string}
     */
    private function getBackgroundPosition(): array
    {
        $parsed = $this->parseBackgroundPosition();

        return [
            'posX' => $this->getAttribute('background-position-x') ?? $parsed['x'],
            'posY' => $this->getAttribute('background-position-y') ?? $parsed['y'],
        ];
    }

    /**
     * @return array{x: string, y: string}
     */
    private function parseBackgroundPosition(): array
    {
        $position = $this->getAttribute('background-position') ?? 'top center';
        $parts = preg_split('/\s+/', trim($position));

        if (false === $parts) {
            return ['x' => 'center', 'y' => 'top'];
        }

        if (1 === \count($parts)) {
            $val = $parts[0];
            if (\in_array($val, ['top', 'bottom'], true)) {
                return ['x' => 'center', 'y' => $val];
            }

            return ['x' => $val, 'y' => 'center'];
        }

        if (2 === \count($parts)) {
            $val1 = $parts[0];
            $val2 = $parts[1];

            if (\in_array($val1, ['top', 'bottom'], true)
                || ('center' === $val1 && \in_array($val2, ['left', 'right'], true))) {
                return ['x' => $val2, 'y' => $val1];
            }

            return ['x' => $val1, 'y' => $val2];
        }

        return ['x' => 'center', 'y' => 'top'];
    }

    private function hasBackground(): bool
    {
        return null !== $this->getAttribute('background-url');
    }

    private function isFullWidth(): bool
    {
        return 'full-width' === $this->getAttribute('full-width');
    }

    private function hasBorderRadius(): bool
    {
        $borderRadius = $this->getAttribute('border-radius');

        return null !== $borderRadius && '' !== $borderRadius;
    }

    private function renderBefore(): string
    {
        $containerWidth = $this->context->containerWidth ?? 600;
        $isFirstSection = $this->isFirstSection();
        $hasGap = $this->hasGap();

        $bgcolorAttr = null !== $this->getAttribute('background-color') && !$hasGap
            ? ['bgcolor' => $this->getAttribute('background-color')]
            : [];

        $cssClass = $this->getAttribute('css-class');
        $outlookClass = (null !== $cssClass && '' !== $cssClass)
            ? CssHelper::suffixCssClasses($cssClass, 'outlook')
            : '';

        $tableStyle = ['width' => "{$containerWidth}px"];
        if (!$isFirstSection && $hasGap) {
            $tableStyle['padding-top'] = $this->getGap();
        }

        $tableAttributes = array_merge([
            'align' => 'center',
            'border' => '0',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'class' => $outlookClass,
            'role' => 'presentation',
            'style' => $tableStyle,
            'width' => $containerWidth,
        ], $bgcolorAttr);

        $tableHtml = \sprintf(
            '<table %s><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">',
            $this->htmlAttributes($tableAttributes),
        );

        return ConditionalTag::START_CONDITIONAL.$tableHtml.ConditionalTag::END_CONDITIONAL;
    }

    private function renderAfter(): string
    {
        return ConditionalTag::START_CONDITIONAL.'</td></tr></table>'.ConditionalTag::END_CONDITIONAL;
    }

    private function renderWrappedChildren(): string
    {
        $output = ConditionalTag::START_CONDITIONAL.'<tr>'.ConditionalTag::END_CONDITIONAL;

        foreach ($this->children as $child) {
            if ($child instanceof BodyComponent && $child::isRawElement()) {
                $output .= $child->render();
            } elseif ($child instanceof ComponentInterface) {
                $output .= $this->renderWrappedChild($child);
            }
        }

        $output .= ConditionalTag::START_CONDITIONAL.'</tr>'.ConditionalTag::END_CONDITIONAL;

        return $output;
    }

    private function renderWrappedChild(ComponentInterface $child): string
    {
        $cssClass = $child->getAttribute('css-class');
        $outlookClass = (null !== $cssClass && '' !== $cssClass)
            ? CssHelper::suffixCssClasses($cssClass, 'outlook')
            : '';

        $tdAttributes = [
            'class' => $outlookClass,
            'style' => null,
        ];

        // Get tdOutlook style from child if available (for Column components)
        if ($child instanceof BodyComponent) {
            $childStyles = $child->getStyles();
            if (isset($childStyles['tdOutlook'])) {
                $tdAttributes['style'] = $childStyles['tdOutlook'];
            }
        }

        return ConditionalTag::START_CONDITIONAL
            .\sprintf('<td %s>', $this->htmlAttributes($tdAttributes))
            .ConditionalTag::END_CONDITIONAL
            .$child->render()
            .ConditionalTag::START_CONDITIONAL
            .'</td>'
            .ConditionalTag::END_CONDITIONAL;
    }

    private function renderWithBackground(string $content): string
    {
        $fullWidth = $this->isFullWidth();
        $containerWidth = $this->context->containerWidth ?? 600;

        $isPercentage = static fn (string $str): bool => (bool) preg_match('/^\d+(\.\d+)?%$/', $str);

        $pos = $this->getBackgroundPosition();
        $bgPosX = $pos['posX'];
        $bgPosY = $pos['posY'];

        // Convert position keywords to percentages
        $bgPosX = match ($bgPosX) {
            'left' => '0%',
            'center' => '50%',
            'right' => '100%',
            default => $isPercentage($bgPosX) ? $bgPosX : '50%',
        };
        $bgPosY = match ($bgPosY) {
            'top' => '0%',
            'center' => '50%',
            'bottom' => '100%',
            default => $isPercentage($bgPosY) ? $bgPosY : '0%',
        };

        $bgRepeat = 'repeat' === $this->getAttribute('background-repeat');

        // Calculate VML origin and position
        $calculateVmlValues = static function (string $pos, bool $isX) use ($isPercentage, $bgRepeat): array {
            if ($isPercentage($pos)) {
                preg_match('/^(\d+(\.\d+)?)%$/', $pos, $matches);
                $decimal = (float) $matches[1] / 100;

                if ($bgRepeat) {
                    return [$decimal, $decimal];
                }

                $value = (-50 + $decimal * 100) / 100;

                return [$value, $value];
            }

            if ($bgRepeat) {
                return $isX ? [0.5, 0.5] : [0, 0];
            }

            return $isX ? [0, 0] : [-0.5, -0.5];
        };

        [$vOriginX, $vPosX] = $calculateVmlValues($bgPosX, isX: true);
        [$vOriginY, $vPosY] = $calculateVmlValues($bgPosY, isX: false);

        // Handle background-size
        $vSizeAttributes = [];
        $backgroundSize = $this->getAttribute('background-size');

        if ('cover' === $backgroundSize || 'contain' === $backgroundSize) {
            $vSizeAttributes = [
                'size' => '1,1',
                'aspect' => 'cover' === $backgroundSize ? 'atleast' : 'atmost',
            ];
        } elseif ('auto' !== $backgroundSize) {
            $bgSplit = preg_split('/\s+/', $backgroundSize);

            if (false === $bgSplit || 1 === \count($bgSplit)) {
                $vSizeAttributes = [
                    'size' => $backgroundSize,
                    'aspect' => 'atmost',
                ];
            } else {
                $vSizeAttributes = [
                    'size' => implode(',', $bgSplit),
                ];
            }
        }

        $vmlType = 'no-repeat' === $this->getAttribute('background-repeat') ? 'frame' : 'tile';

        if ('auto' === $backgroundSize) {
            $vmlType = 'tile';
            $vOriginX = 0.5;
            $vPosX = 0.5;
            $vOriginY = 0;
            $vPosY = 0;
        }

        $vRectStyle = $fullWidth
            ? ['mso-width-percent' => '1000']
            : ['width' => "{$containerWidth}px"];

        $vRectAttributes = array_merge([
            'style' => $vRectStyle,
            'xmlns:v' => 'urn:schemas-microsoft-com:vml',
            'fill' => 'true',
            'stroke' => 'false',
        ]);

        $vFillAttributes = array_merge([
            'origin' => "{$vOriginX}, {$vOriginY}",
            'position' => "{$vPosX}, {$vPosY}",
            'src' => $this->getAttribute('background-url'),
            'color' => $this->getAttribute('background-color'),
            'type' => $vmlType,
        ], $vSizeAttributes);

        $vmlStart = \sprintf(
            '<v:rect %s><v:fill %s /><v:textbox style="mso-fit-shape-to-text:true" inset="0,0,0,0">',
            $this->htmlAttributes($vRectAttributes),
            $this->htmlAttributes($vFillAttributes),
        );

        $vmlEnd = '</v:textbox></v:rect>';

        return ConditionalTag::START_CONDITIONAL
            .$vmlStart
            .ConditionalTag::END_CONDITIONAL
            .$content
            .ConditionalTag::START_CONDITIONAL
            .$vmlEnd
            .ConditionalTag::END_CONDITIONAL;
    }

    private function renderSection(): string
    {
        $hasBackground = $this->hasBackground();

        $divAttributes = [
            'style' => 'div',
        ];

        if (!$this->isFullWidth()) {
            $cssClass = $this->getAttribute('css-class');
            if (null !== $cssClass && '' !== $cssClass) {
                $divAttributes['class'] = $cssClass;
            }
        }

        $innerDivStart = $hasBackground ? \sprintf('<div %s>', $this->htmlAttributes(['style' => 'innerDiv'])) : '';
        $innerDivEnd = $hasBackground ? '</div>' : '';

        $tableAttributes = [
            'align' => 'center',
            'border' => '0',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'role' => 'presentation',
            'style' => 'table',
        ];

        if (!$this->isFullWidth() && $this->hasBackground()) {
            $tableAttributes['background'] = $this->getAttribute('background-url');
        }

        $innerTable = ConditionalTag::START_CONDITIONAL
            .'<table role="presentation" border="0" cellpadding="0" cellspacing="0">'
            .ConditionalTag::END_CONDITIONAL
            .$this->renderWrappedChildren()
            .ConditionalTag::START_CONDITIONAL
            .'</table>'
            .ConditionalTag::END_CONDITIONAL;

        return \sprintf(
            '<div %s>%s<table %s><tbody><tr><td %s>%s</td></tr></tbody></table>%s</div>',
            $this->htmlAttributes($divAttributes),
            $innerDivStart,
            $this->htmlAttributes($tableAttributes),
            $this->htmlAttributes(['style' => 'td']),
            $innerTable,
            $innerDivEnd,
        );
    }

    private function renderFullWidth(): string
    {
        $section = $this->renderBefore().$this->renderSection().$this->renderAfter();
        $content = $this->hasBackground()
            ? $this->renderWithBackground($section)
            : $section;

        $tableAttributes = [
            'align' => 'center',
            'border' => '0',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'role' => 'presentation',
            'style' => 'tableFullwidth',
        ];

        $cssClass = $this->getAttribute('css-class');
        if (null !== $cssClass && '' !== $cssClass) {
            $tableAttributes['class'] = $cssClass;
        }

        if ($this->hasBackground()) {
            $tableAttributes['background'] = $this->getAttribute('background-url');
        }

        return \sprintf(
            '<table %s><tbody><tr><td>%s</td></tr></tbody></table>',
            $this->htmlAttributes($tableAttributes),
            $content,
        );
    }

    private function renderSimple(): string
    {
        $section = $this->renderSection();

        return $this->renderBefore()
            .($this->hasBackground() ? $this->renderWithBackground($section) : $section)
            .$this->renderAfter();
    }
}
