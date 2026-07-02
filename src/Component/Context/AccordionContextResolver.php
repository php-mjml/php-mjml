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

namespace PhpMjml\Component\Context;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Validates and resolves accordion context settings.
 *
 * Accordion settings are passed from mj-accordion → mj-accordion-element → mj-accordion-title/text.
 */
final class AccordionContextResolver
{
    public const KEY = 'accordion';

    /**
     * Maps MJML attribute names (kebab-case) to accordion settings keys (camelCase).
     *
     * Single source of truth for the icon attributes shared between
     * mj-accordion, mj-accordion-element and mj-accordion-title/text.
     */
    public const ATTRIBUTE_MAP = [
        'border' => 'border',
        'icon-align' => 'iconAlign',
        'icon-width' => 'iconWidth',
        'icon-height' => 'iconHeight',
        'icon-position' => 'iconPosition',
        'icon-wrapped-url' => 'iconWrappedUrl',
        'icon-wrapped-alt' => 'iconWrappedAlt',
        'icon-unwrapped-url' => 'iconUnwrappedUrl',
        'icon-unwrapped-alt' => 'iconUnwrappedAlt',
    ];

    /**
     * Settings keys that hold font-family values in addition to the mapped icon attributes.
     */
    private const FONT_KEYS = ['fontFamily', 'elementFontFamily'];

    /**
     * Resolve accordion settings with validation.
     *
     * @param array<string, mixed> $data Raw settings data
     *
     * @return array<string, string|null> Validated settings
     */
    public static function resolve(array $data): array
    {
        $resolver = new OptionsResolver();
        self::configureOptions($resolver);

        return $resolver->resolve($data);
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $settingsKeys = array_merge(self::FONT_KEYS, array_values(self::ATTRIBUTE_MAP));

        $resolver->setDefaults(array_fill_keys($settingsKeys, null));

        foreach ($settingsKeys as $key) {
            $resolver->setAllowedTypes($key, ['null', 'string']);
        }

        $resolver->setAllowedValues('iconAlign', [null, 'top', 'middle', 'bottom']);
        $resolver->setAllowedValues('iconPosition', [null, 'left', 'right']);
    }

    /**
     * Merge parent accordion settings with element-specific settings.
     *
     * @param array<string, string|null>|null $parentSettings  Settings from parent accordion
     * @param array<string, string|null>      $elementSettings Settings from accordion element
     *
     * @return array<string, string|null> Merged settings
     */
    public static function mergeSettings(?array $parentSettings, array $elementSettings): array
    {
        $base = $parentSettings ?? [];

        return self::resolve(array_merge($base, $elementSettings));
    }
}
