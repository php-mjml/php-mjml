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
use PhpMjml\Helper\CssHelper;
use PhpMjml\Helper\WidthParser;

final class Group extends BodyComponent
{
    public static function getComponentName(): string
    {
        return 'mj-group';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'background-color' => 'color',
            'direction' => 'enum(ltr,rtl)',
            'vertical-align' => 'enum(top,bottom,middle)',
            'width' => 'unit(px,%)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'direction' => 'ltr',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getChildContext(): array
    {
        $context = $this->context?->toArray() ?? [];
        $parentWidth = $this->context->containerWidth ?? 600;
        $nonRawSiblings = $this->props['nonRawSiblings'] ?? 1;

        $paddingSize = $this->getShorthandAttrValue('padding', 'left')
            + $this->getShorthandAttrValue('padding', 'right');

        $width = $this->getAttribute('width');
        $containerWidth = $width ?? \sprintf('%dpx', (int) ($parentWidth / $nonRawSiblings));

        $parsed = WidthParser::parse($containerWidth, parseFloatToInt: false);
        $unit = $parsed['unit'];
        $parsedWidth = $parsed['parsedWidth'];

        if ('%' === $unit) {
            $containerWidth = \sprintf('%dpx', (int) (($parentWidth * $parsedWidth) / 100 - $paddingSize));
        } else {
            $containerWidth = \sprintf('%dpx', (int) ($parsedWidth - $paddingSize));
        }

        $context['containerWidth'] = (int) $containerWidth;
        $context['nonRawSiblings'] = \count($this->children);
        // Pass mobileWidth to children so columns inside groups use their actual width on mobile
        $context['inheritedAttributes'] = ['mobileWidth' => 'mobileWidth'];

        return $context;
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        return [
            'div' => [
                'font-size' => '0',
                'line-height' => '0',
                'text-align' => 'left',
                'display' => 'inline-block',
                'width' => '100%',
                'direction' => $this->getAttribute('direction'),
                'vertical-align' => $this->getAttribute('vertical-align'),
                'background-color' => $this->getAttribute('background-color'),
            ],
            'tdOutlook' => [
                'vertical-align' => $this->getAttribute('vertical-align'),
                'width' => $this->getWidthAsPixel(),
            ],
        ];
    }

    public function render(): string
    {
        $classesName = $this->getColumnClass().' mj-outlook-group-fix';

        $cssClass = $this->getAttribute('css-class');
        if (null !== $cssClass && '' !== $cssClass) {
            $classesName .= ' '.$cssClass;
        }

        $backgroundColor = $this->getAttribute('background-color');
        $bgcolorAttr = (null !== $backgroundColor && 'none' !== $backgroundColor)
            ? ['bgcolor' => $backgroundColor]
            : [];

        $tableAttributes = array_merge([
            'border' => '0',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'role' => 'presentation',
        ], $bgcolorAttr);

        $outlookTableStart = ConditionalTag::START_CONDITIONAL
            .\sprintf('<table %s><tr>', $this->htmlAttributes($tableAttributes))
            .ConditionalTag::END_CONDITIONAL;

        $outlookTableEnd = ConditionalTag::START_CONDITIONAL
            .'</tr></table>'
            .ConditionalTag::END_CONDITIONAL;

        return \sprintf(
            '<div %s>%s%s%s</div>',
            $this->htmlAttributes([
                'class' => $classesName,
                'style' => 'div',
            ]),
            $outlookTableStart,
            $this->renderChildren(),
            $outlookTableEnd,
        );
    }

    /**
     * Custom children rendering for group (wraps non-raw elements in Outlook TD).
     */
    protected function renderChildren(): string
    {
        $output = '';
        $groupWidth = $this->getChildContext()['containerWidth'];

        foreach ($this->children as $child) {
            if ($child instanceof BodyComponent && $child::isRawElement()) {
                $output .= $child->render();
            } elseif ($child instanceof ComponentInterface) {
                $output .= $this->renderWrappedChild($child, $groupWidth);
            }
        }

        return $output;
    }

    private function renderWrappedChild(ComponentInterface $child, int $groupWidth): string
    {
        $elementWidth = $this->getElementWidth($child, $groupWidth);

        $cssClass = $child->getAttribute('css-class');
        $outlookClass = (null !== $cssClass && '' !== $cssClass)
            ? CssHelper::suffixCssClasses($cssClass, 'outlook')
            : null;

        $tdAttributes = [
            'style' => [
                'align' => $child->getAttribute('align'),
                'vertical-align' => $child->getAttribute('vertical-align'),
                'width' => $elementWidth,
            ],
        ];

        // Only add class attribute if it has a value (avoids empty class="")
        if (null !== $outlookClass && '' !== $outlookClass) {
            $tdAttributes['class'] = $outlookClass;
        }

        return ConditionalTag::START_CONDITIONAL
            .\sprintf('<td %s>', $this->htmlAttributes($tdAttributes))
            .ConditionalTag::END_CONDITIONAL
            .$child->render()
            .ConditionalTag::START_CONDITIONAL
            .'</td>'
            .ConditionalTag::END_CONDITIONAL;
    }

    private function getElementWidth(ComponentInterface $child, int $groupWidth): string
    {
        $containerWidth = $this->context->containerWidth ?? 600;
        $nonRawSiblings = $this->props['nonRawSiblings'] ?? 1;

        // Try to get width from child - either through getWidthAsPixel method or width attribute
        $childWidth = null;

        if ($child instanceof Column) {
            // Column has getWidthAsPixel method
            $childStyles = $child->getStyles();
            $childWidth = $childStyles['tdOutlook']['width'] ?? null;
        } else {
            $childWidth = $child->getAttribute('width');
        }

        if (null === $childWidth || '' === $childWidth) {
            return \sprintf('%dpx', (int) ($containerWidth / $nonRawSiblings));
        }

        $parsed = WidthParser::parse((string) $childWidth, parseFloatToInt: false);

        if ('%' === $parsed['unit']) {
            return \sprintf('%dpx', (int) ((100 * $parsed['parsedWidth']) / $groupWidth));
        }

        return \sprintf('%d%s', (int) $parsed['parsedWidth'], $parsed['unit']);
    }

    /**
     * @return array{parsedWidth: float|int, unit: string}
     */
    private function getParsedWidth(): array
    {
        $nonRawSiblings = $this->props['nonRawSiblings'] ?? 1;

        $width = $this->getAttribute('width') ?? \sprintf('%d%%', (int) (100 / $nonRawSiblings));

        return WidthParser::parse($width, parseFloatToInt: false);
    }

    private function getWidthAsPixel(): string
    {
        $containerWidth = $this->context->containerWidth ?? 600;
        $parsed = $this->getParsedWidth();

        if ('%' === $parsed['unit']) {
            return \sprintf('%dpx', (int) (($containerWidth * $parsed['parsedWidth']) / 100));
        }

        return \sprintf('%dpx', (int) $parsed['parsedWidth']);
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
}
