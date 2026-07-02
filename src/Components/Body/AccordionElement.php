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
use PhpMjml\Component\Context\AccordionContextResolver;
use PhpMjml\Helper\ConditionalTag;

final class AccordionElement extends BodyComponent
{
    use AccordionSettingsTrait;

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

        // Merge with parent accordion settings, adding element-specific settings
        $parentSettings = $this->getAccordionSettings();
        $elementSettings = ['elementFontFamily' => $this->getAttribute('font-family')];
        foreach (AccordionContextResolver::ATTRIBUTE_MAP as $attributeName => $settingsKey) {
            $elementSettings[$settingsKey] = $this->getIconAttribute($attributeName);
        }

        $accordionData = AccordionContextResolver::mergeSettings($parentSettings, $elementSettings);

        $context['componentData'][AccordionContextResolver::KEY] = $accordionData;

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
                if (AccordionTitle::getComponentName() === $tagName) {
                    $hasTitle = true;
                }
                if (AccordionText::getComponentName() === $tagName) {
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
     * @return array<string, string|null>
     */
    private function getIconAttributes(): array
    {
        $attributes = [];
        foreach (array_keys(AccordionContextResolver::ATTRIBUTE_MAP) as $attributeName) {
            $attributes[$attributeName] = $this->getIconAttribute($attributeName);
        }

        return $attributes;
    }
}
