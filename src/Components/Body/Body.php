<?php

declare(strict_types=1);

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
        if ($backgroundColor !== null && $this->context !== null) {
            $this->context->setBackgroundColor($backgroundColor);
        }

        $attributes = [
            'style' => 'div',
        ];

        // Add aria attributes
        if ($this->context?->title !== null && $this->context->title !== '') {
            $attributes['aria-label'] = $this->context->title;
        }
        $attributes['aria-roledescription'] = 'email';
        $attributes['role'] = 'article';

        // Add css-class if set
        $cssClass = $this->getAttribute('css-class');
        if ($cssClass !== null && $cssClass !== '') {
            $attributes['class'] = $cssClass;
        }

        // Add language attributes
        if ($this->context?->lang !== null) {
            $attributes['lang'] = $this->context->lang;
        }
        if ($this->context?->dir !== null) {
            $attributes['dir'] = $this->context->dir;
        }

        return sprintf(
            '<div %s>%s</div>',
            $this->htmlAttributes($attributes),
            $this->renderChildren(),
        );
    }
}
