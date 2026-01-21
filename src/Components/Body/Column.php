<?php

declare(strict_types=1);

namespace PhpMjml\Components\Body;

use PhpMjml\Component\BodyComponent;
use PhpMjml\Component\ComponentInterface;
use PhpMjml\Helper\WidthParser;

final class Column extends BodyComponent
{
    public static function getComponentName(): string
    {
        return 'mj-column';
    }

    public static function getAllowedAttributes(): array
    {
        return [
            'background-color' => 'color',
            'border' => 'string',
            'border-bottom' => 'string',
            'border-left' => 'string',
            'border-radius' => 'unit(px,%){1,4}',
            'border-right' => 'string',
            'border-top' => 'string',
            'direction' => 'enum(ltr,rtl)',
            'inner-background-color' => 'color',
            'padding-bottom' => 'unit(px,%)',
            'padding-left' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'padding-top' => 'unit(px,%)',
            'inner-border' => 'string',
            'inner-border-bottom' => 'string',
            'inner-border-left' => 'string',
            'inner-border-radius' => 'unit(px,%){1,4}',
            'inner-border-right' => 'string',
            'inner-border-top' => 'string',
            'padding' => 'unit(px,%){1,4}',
            'vertical-align' => 'enum(top,bottom,middle)',
            'width' => 'unit(px,%)',
        ];
    }

    public static function getDefaultAttributes(): array
    {
        return [
            'direction' => 'ltr',
            'vertical-align' => 'top',
        ];
    }

    public function getChildContext(): array
    {
        $context = $this->context?->toArray() ?? [];
        $parentWidth = $this->context?->containerWidth ?? 600;
        $nonRawSiblings = $this->props['nonRawSiblings'] ?? 1;

        $boxWidths = $this->getBoxWidths();
        $paddings = $boxWidths['paddings'];
        $borders = $boxWidths['borders'];

        $innerBorders = $this->getShorthandBorderValue('left', 'inner-border')
            + $this->getShorthandBorderValue('right', 'inner-border');

        $allPaddings = $paddings + $borders + $innerBorders;

        $width = $this->getAttribute('width');
        $containerWidth = $width ?? sprintf('%dpx', (int) ($parentWidth / $nonRawSiblings));

        $parsed = WidthParser::parse($containerWidth, parseFloatToInt: false);
        $unit = $parsed['unit'];
        $parsedWidth = $parsed['parsedWidth'];

        if ($unit === '%') {
            $containerWidth = sprintf('%dpx', (int) (($parentWidth * $parsedWidth) / 100 - $allPaddings));
        } else {
            $containerWidth = sprintf('%dpx', (int) ($parsedWidth - $allPaddings));
        }

        $context['containerWidth'] = (int) $containerWidth;

        return $context;
    }

    public function getStyles(): array
    {
        $hasBorderRadius = $this->hasBorderRadius();
        $hasInnerBorderRadius = $this->hasInnerBorderRadius();

        $tableStyle = [
            'background-color' => $this->getAttribute('background-color'),
            'border' => $this->getAttribute('border'),
            'border-bottom' => $this->getAttribute('border-bottom'),
            'border-left' => $this->getAttribute('border-left'),
            'border-radius' => $this->getAttribute('border-radius'),
            'border-right' => $this->getAttribute('border-right'),
            'border-top' => $this->getAttribute('border-top'),
            'vertical-align' => $this->getAttribute('vertical-align'),
        ];

        if ($hasBorderRadius) {
            $tableStyle['border-collapse'] = 'separate';
        }

        $table = $this->hasGutter()
            ? [
                'background-color' => $this->getAttribute('inner-background-color'),
                'border' => $this->getAttribute('inner-border'),
                'border-bottom' => $this->getAttribute('inner-border-bottom'),
                'border-left' => $this->getAttribute('inner-border-left'),
                'border-radius' => $this->getAttribute('inner-border-radius'),
                'border-right' => $this->getAttribute('inner-border-right'),
                'border-top' => $this->getAttribute('inner-border-top'),
            ]
            : $tableStyle;

        if ($hasInnerBorderRadius) {
            $table['border-collapse'] = 'separate';
        }

        return [
            'div' => [
                'font-size' => '0px',
                'text-align' => 'left',
                'direction' => $this->getAttribute('direction'),
                'display' => 'inline-block',
                'vertical-align' => $this->getAttribute('vertical-align'),
                'width' => $this->getMobileWidth(),
            ],
            'table' => $table,
            'tdOutlook' => [
                'vertical-align' => $this->getAttribute('vertical-align'),
                'width' => $this->getWidthAsPixel(),
            ],
            'gutter' => array_merge($tableStyle, [
                'padding' => $this->getAttribute('padding'),
                'padding-top' => $this->getAttribute('padding-top'),
                'padding-right' => $this->getAttribute('padding-right'),
                'padding-bottom' => $this->getAttribute('padding-bottom'),
                'padding-left' => $this->getAttribute('padding-left'),
            ]),
        ];
    }

    private function getMobileWidth(): string
    {
        $nonRawSiblings = $this->props['nonRawSiblings'] ?? 1;
        $width = $this->getAttribute('width');
        $mobileWidth = $this->getAttribute('mobileWidth');

        if ($mobileWidth !== 'mobileWidth') {
            return '100%';
        }

        if ($width === null) {
            return sprintf('%d%%', (int) (100 / $nonRawSiblings));
        }

        $parsed = WidthParser::parse($width, parseFloatToInt: false);

        return match ($parsed['unit']) {
            '%' => $width,
            'px' => sprintf('%d%%', (int) (($parsed['parsedWidth'] / ($this->context?->containerWidth ?? 600)) * 100)),
            default => sprintf('%d%%', (int) (($parsed['parsedWidth'] / ($this->context?->containerWidth ?? 600)) * 100)),
        };
    }

    private function getWidthAsPixel(): string
    {
        $containerWidth = $this->context?->containerWidth ?? 600;
        $parsedWidth = $this->getParsedWidth(toString: true);

        $parsed = WidthParser::parse($parsedWidth, parseFloatToInt: false);

        if ($parsed['unit'] === '%') {
            return sprintf('%dpx', (int) (($containerWidth * $parsed['parsedWidth']) / 100));
        }

        return sprintf('%dpx', (int) $parsed['parsedWidth']);
    }

    /**
     * @return array{parsedWidth: float|int, unit: string}|string
     */
    private function getParsedWidth(bool $toString = false): array|string
    {
        $nonRawSiblings = $this->props['nonRawSiblings'] ?? 1;

        $width = $this->getAttribute('width') ?? sprintf('%d%%', (int) (100 / $nonRawSiblings));

        $parsed = WidthParser::parse($width, parseFloatToInt: false);

        if ($toString) {
            return sprintf('%s%s', $parsed['parsedWidth'], $parsed['unit']);
        }

        return $parsed;
    }

    private function getColumnClass(): string
    {
        $parsed = $this->getParsedWidth();
        $parsedWidth = $parsed['parsedWidth'];
        $unit = $parsed['unit'];

        $formattedClassNb = str_replace('.', '-', (string) $parsedWidth);

        $className = match ($unit) {
            '%' => "mj-column-per-{$formattedClassNb}",
            'px' => "mj-column-px-{$formattedClassNb}",
            default => "mj-column-px-{$formattedClassNb}",
        };

        // Register media query
        $this->context?->addMediaQuery($className, [
            'parsedWidth' => $parsedWidth,
            'unit' => $unit,
        ]);

        return $className;
    }

    private function hasBorderRadius(): bool
    {
        $borderRadius = $this->getAttribute('border-radius');

        return $borderRadius !== null && $borderRadius !== '';
    }

    private function hasInnerBorderRadius(): bool
    {
        $innerBorderRadius = $this->getAttribute('inner-border-radius');

        return $innerBorderRadius !== null && $innerBorderRadius !== '';
    }

    private function hasGutter(): bool
    {
        $paddingAttrs = ['padding', 'padding-bottom', 'padding-left', 'padding-right', 'padding-top'];

        foreach ($paddingAttrs as $attr) {
            if ($this->getAttribute($attr) !== null) {
                return true;
            }
        }

        return false;
    }

    private function renderGutter(): string
    {
        $hasBorderRadius = $this->hasBorderRadius();

        $tableAttributes = [
            'border' => '0',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'role' => 'presentation',
            'width' => '100%',
        ];

        if ($hasBorderRadius) {
            $tableAttributes['style'] = ['border-collapse' => 'separate'];
        }

        return sprintf(
            '<table %s><tbody><tr><td %s>%s</td></tr></tbody></table>',
            $this->htmlAttributes($tableAttributes),
            $this->htmlAttributes(['style' => 'gutter']),
            $this->renderColumn(),
        );
    }

    private function renderColumn(): string
    {
        $rows = '';

        foreach ($this->children as $child) {
            if ($child instanceof BodyComponent && $child::isRawElement()) {
                $rows .= $child->render();
            } elseif ($child instanceof ComponentInterface) {
                $rows .= $this->renderChildInRow($child);
            }
        }

        return sprintf(
            '<table %s><tbody>%s</tbody></table>',
            $this->htmlAttributes([
                'border' => '0',
                'cellpadding' => '0',
                'cellspacing' => '0',
                'role' => 'presentation',
                'style' => 'table',
                'width' => '100%',
            ]),
            $rows,
        );
    }

    private function renderChildInRow(ComponentInterface $child): string
    {
        $tdAttributes = [
            'align' => $child->getAttribute('align'),
            'style' => [
                'background' => $child->getAttribute('container-background-color'),
                'font-size' => '0px',
                'padding' => $child->getAttribute('padding'),
                'padding-top' => $child->getAttribute('padding-top'),
                'padding-right' => $child->getAttribute('padding-right'),
                'padding-bottom' => $child->getAttribute('padding-bottom'),
                'padding-left' => $child->getAttribute('padding-left'),
                'word-break' => 'break-word',
            ],
        ];

        $cssClass = $child->getAttribute('css-class');
        if ($cssClass !== null && $cssClass !== '') {
            $tdAttributes['class'] = $cssClass;
        }

        return sprintf(
            '<tr><td %s>%s</td></tr>',
            $this->htmlAttributes($tdAttributes),
            $child->render(),
        );
    }

    public function render(): string
    {
        $classesName = $this->getColumnClass() . ' mj-outlook-group-fix';

        $cssClass = $this->getAttribute('css-class');
        if ($cssClass !== null && $cssClass !== '') {
            $classesName .= ' ' . $cssClass;
        }

        return sprintf(
            '<div %s>%s</div>',
            $this->htmlAttributes([
                'class' => $classesName,
                'style' => 'div',
            ]),
            $this->hasGutter() ? $this->renderGutter() : $this->renderColumn(),
        );
    }
}
