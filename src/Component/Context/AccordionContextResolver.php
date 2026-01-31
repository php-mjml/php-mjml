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
        $resolver->setDefaults([
            'fontFamily' => null,
            'elementFontFamily' => null,
            'border' => null,
            'iconAlign' => null,
            'iconWidth' => null,
            'iconHeight' => null,
            'iconPosition' => null,
            'iconWrappedUrl' => null,
            'iconWrappedAlt' => null,
            'iconUnwrappedUrl' => null,
            'iconUnwrappedAlt' => null,
        ]);

        $resolver->setAllowedTypes('fontFamily', ['null', 'string']);
        $resolver->setAllowedTypes('elementFontFamily', ['null', 'string']);
        $resolver->setAllowedTypes('border', ['null', 'string']);
        $resolver->setAllowedTypes('iconAlign', ['null', 'string']);
        $resolver->setAllowedTypes('iconWidth', ['null', 'string']);
        $resolver->setAllowedTypes('iconHeight', ['null', 'string']);
        $resolver->setAllowedTypes('iconPosition', ['null', 'string']);
        $resolver->setAllowedTypes('iconWrappedUrl', ['null', 'string']);
        $resolver->setAllowedTypes('iconWrappedAlt', ['null', 'string']);
        $resolver->setAllowedTypes('iconUnwrappedUrl', ['null', 'string']);
        $resolver->setAllowedTypes('iconUnwrappedAlt', ['null', 'string']);

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
