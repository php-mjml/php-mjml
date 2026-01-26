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

final class AccordionElement extends BodyComponent
{
    protected static bool $endingTag = false;

    public static function getComponentName(): string
    {
        return 'mj-accordion-element';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'background-color' => 'color',
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
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public static function getDefaultAttributes(): array
    {
        return [];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        return [
            'td' => [
                'padding' => '0px',
                'background-color' => $this->getAttribute('background-color'),
            ],
            'label' => [
                'font-size' => '13px',
                'font-family' => $this->getAttribute('font-family'),
            ],
            'input' => [
                'display' => 'none',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getChildContext(): array
    {
        $context = parent::getChildContext();
        $context['elementFontFamily'] = $this->getAttribute('font-family');

        // Pass icon attributes from parent accordion or own attributes
        $context['accordionBorder'] = $this->getIconAttribute('border');
        $context['accordionIconAlign'] = $this->getIconAttribute('icon-align');
        $context['accordionIconWidth'] = $this->getIconAttribute('icon-width');
        $context['accordionIconHeight'] = $this->getIconAttribute('icon-height');
        $context['accordionIconPosition'] = $this->getIconAttribute('icon-position');
        $context['accordionIconWrappedUrl'] = $this->getIconAttribute('icon-wrapped-url');
        $context['accordionIconWrappedAlt'] = $this->getIconAttribute('icon-wrapped-alt');
        $context['accordionIconUnwrappedUrl'] = $this->getIconAttribute('icon-unwrapped-url');
        $context['accordionIconUnwrappedAlt'] = $this->getIconAttribute('icon-unwrapped-alt');

        return $context;
    }

    public function render(): string
    {
        $inputHtml = ConditionalTag::wrap(
            \sprintf(
                '<input %s />',
                $this->htmlAttributes([
                    'class' => 'mj-accordion-checkbox',
                    'type' => 'checkbox',
                    'style' => 'input',
                ])
            ),
            true
        );

        return \sprintf(
            '<tr %s><td %s><label %s>%s<div>%s</div></label></td></tr>',
            $this->htmlAttributes([
                'class' => $this->getAttribute('css-class'),
            ]),
            $this->htmlAttributes(['style' => 'td']),
            $this->htmlAttributes([
                'class' => 'mj-accordion-element',
                'style' => 'label',
            ]),
            $inputHtml,
            $this->handleMissingChildren(),
        );
    }

    private function handleMissingChildren(): string
    {
        $hasTitle = false;
        $hasText = false;

        foreach ($this->children as $child) {
            if ($child instanceof ComponentInterface) {
                $tagName = $child::getComponentName();
                if ('mj-accordion-title' === $tagName) {
                    $hasTitle = true;
                }
                if ('mj-accordion-text' === $tagName) {
                    $hasText = true;
                }
            }
        }

        $result = [];

        // Add default title if missing
        if (!$hasTitle) {
            $result[] = $this->renderDefaultTitle();
        }

        // Render children
        $result[] = $this->renderChildren();

        // Add default text if missing
        if (!$hasText) {
            $result[] = $this->renderDefaultText();
        }

        return implode("\n", $result);
    }

    private function renderDefaultTitle(): string
    {
        if (null === $this->context) {
            return '';
        }

        $title = new AccordionTitle(
            attributes: $this->getIconAttributes(),
            children: [],
            content: '',
            context: $this->context,
        );

        return $title->render();
    }

    private function renderDefaultText(): string
    {
        if (null === $this->context) {
            return '';
        }

        $text = new AccordionText(
            attributes: $this->getIconAttributes(),
            children: [],
            content: '',
            context: $this->context,
        );

        return $text->render();
    }

    /**
     * Get icon attribute from own attributes or parent accordion context.
     */
    private function getIconAttribute(string $name): ?string
    {
        // First check own attributes
        $value = $this->getAttribute($name);
        if (null !== $value) {
            return $value;
        }

        // Fall back to parent accordion context
        if (null === $this->context) {
            return null;
        }

        return match ($name) {
            'border' => $this->context->accordionBorder,
            'icon-align' => $this->context->accordionIconAlign,
            'icon-width' => $this->context->accordionIconWidth,
            'icon-height' => $this->context->accordionIconHeight,
            'icon-position' => $this->context->accordionIconPosition,
            'icon-wrapped-url' => $this->context->accordionIconWrappedUrl,
            'icon-wrapped-alt' => $this->context->accordionIconWrappedAlt,
            'icon-unwrapped-url' => $this->context->accordionIconUnwrappedUrl,
            'icon-unwrapped-alt' => $this->context->accordionIconUnwrappedAlt,
            default => null,
        };
    }

    /**
     * @return array<string, string|null>
     */
    private function getIconAttributes(): array
    {
        return [
            'border' => $this->getIconAttribute('border'),
            'icon-align' => $this->getIconAttribute('icon-align'),
            'icon-width' => $this->getIconAttribute('icon-width'),
            'icon-height' => $this->getIconAttribute('icon-height'),
            'icon-position' => $this->getIconAttribute('icon-position'),
            'icon-wrapped-url' => $this->getIconAttribute('icon-wrapped-url'),
            'icon-wrapped-alt' => $this->getIconAttribute('icon-wrapped-alt'),
            'icon-unwrapped-url' => $this->getIconAttribute('icon-unwrapped-url'),
            'icon-unwrapped-alt' => $this->getIconAttribute('icon-unwrapped-alt'),
        ];
    }
}
