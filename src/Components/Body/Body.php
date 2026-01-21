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

final class Body extends BodyComponent
{
    public static function getComponentName(): string
    {
        return 'mj-body';
    }

    public static function getAllowedAttributes(): array
    {
        return [
            'width' => 'unit(px)',
            'background-color' => 'color',
        ];
    }

    public static function getDefaultAttributes(): array
    {
        return [
            'width' => '600px',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getChildContext(): array
    {
        $context = $this->context?->toArray() ?? [];

        // Parse width attribute (e.g., "600px" -> 600)
        $width = $this->getAttribute('width');
        $context['containerWidth'] = (int) $width;

        return $context;
    }

    public function getStyles(): array
    {
        return [
            'div' => [
                'background-color' => $this->getAttribute('background-color'),
            ],
        ];
    }

    public function render(): string
    {
        $backgroundColor = $this->getAttribute('background-color');

        // Set background color on context for body tag styling
        if (null !== $backgroundColor && null !== $this->context) {
            $this->context->setBackgroundColor($backgroundColor);
        }

        // Add aria attributes
        $attributes = [];
        if (null !== $this->context?->title && '' !== $this->context->title) {
            $attributes['aria-label'] = $this->context->title;
        }
        $attributes['aria-roledescription'] = 'email';

        // Add css-class if set
        $cssClass = $this->getAttribute('css-class');
        if (null !== $cssClass && '' !== $cssClass) {
            $attributes['class'] = $cssClass;
        }

        $attributes['style'] = 'div';
        $attributes['role'] = 'article';

        // Add language attributes
        $attributes['lang'] = null !== $this->context ? $this->context->lang : 'und';
        $attributes['dir'] = null !== $this->context ? $this->context->dir : 'auto';

        return \sprintf(
            '<div %s>%s</div>',
            $this->htmlAttributes($attributes),
            $this->renderChildren(),
        );
    }
}
