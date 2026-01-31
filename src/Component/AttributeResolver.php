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

namespace PhpMjml\Component;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Converts MJML attribute type strings to OptionsResolver configuration.
 *
 * Attribute types follow patterns like:
 * - 'color' - CSS color value
 * - 'string' - Any string value
 * - 'unit(px,%)' - Value with specific units
 * - 'unit(px,%){1,4}' - Shorthand with 1-4 values
 * - 'enum(left,center,right)' - Specific allowed values
 * - 'unitWithNegative(px,em)' - Unit values that may be negative
 */
final class AttributeResolver
{
    /**
     * @param array<string, string>      $allowedAttributes Attribute names mapped to type strings
     * @param array<string, string|null> $defaults          Default values for attributes
     */
    public static function createResolver(array $allowedAttributes, array $defaults): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults($defaults);

        foreach ($allowedAttributes as $name => $type) {
            $resolver->setDefined($name);
            self::configureType($resolver, $name, $type);
        }

        // Always allow css-class and mj-class
        $resolver->setDefined(['css-class', 'mj-class']);
        $resolver->setAllowedTypes('css-class', ['null', 'string']);
        $resolver->setAllowedTypes('mj-class', ['null', 'string']);

        return $resolver;
    }

    private static function configureType(OptionsResolver $resolver, string $name, string $type): void
    {
        if (str_starts_with($type, 'enum(')) {
            // 'enum(left,center,right)' → setAllowedValues
            $values = self::parseEnumValues($type);
            $resolver->setAllowedValues($name, static fn (mixed $value) => null === $value || \in_array($value, $values, true));
            $resolver->setAllowedTypes($name, ['null', 'string']);
        } elseif ('color' === $type) {
            $resolver->setAllowedTypes($name, ['null', 'string']);
        } elseif ('string' === $type) {
            $resolver->setAllowedTypes($name, ['null', 'string']);
        } elseif ('integer' === $type) {
            $resolver->setAllowedTypes($name, ['null', 'string', 'int']);
        } elseif ('boolean' === $type) {
            $resolver->setAllowedTypes($name, ['null', 'string', 'bool']);
        } elseif (str_starts_with($type, 'unit(') || str_starts_with($type, 'unitWithNegative(')) {
            // Unit types (px, %, em, etc.) - allow any string for now
            $resolver->setAllowedTypes($name, ['null', 'string']);
        } else {
            // Default to nullable string for any unknown type
            $resolver->setAllowedTypes($name, ['null', 'string']);
        }
    }

    /**
     * @return array<string>
     */
    private static function parseEnumValues(string $type): array
    {
        // 'enum(left,center,right)' → ['left', 'center', 'right']
        if (preg_match('/enum\((.+)\)/', $type, $matches)) {
            return explode(',', $matches[1]);
        }

        return [];
    }
}
