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

use PhpMjml\Components\Head\Attributes;

/**
 * Compiles MJML attribute type strings into a validation spec.
 *
 * Attribute types follow patterns like:
 * - 'color' - CSS color value
 * - 'string' - Any string value
 * - 'unit(px,%)' - Value with specific units
 * - 'unit(px,%){1,4}' - Shorthand with 1-4 values
 * - 'enum(left,center,right)' - Specific allowed values
 * - 'unitWithNegative(px,em)' - Unit values that may be negative
 *
 * @phpstan-type AttributeSpec array{
 *     allowed: array<string, true>,
 *     enums: array<string, list<string>>,
 *     defaults: array<string, string|null>,
 * }
 */
final class AttributeResolver
{
    /**
     * Compile a per-component validation spec, cached by the caller.
     *
     * @param array<string, string>      $allowedAttributes Attribute names mapped to type strings
     * @param array<string, string|null> $defaults          Default values for attributes
     *
     * @return AttributeSpec
     */
    public static function createSpec(array $allowedAttributes, array $defaults): array
    {
        $allowed = [];
        foreach ($defaults as $name => $value) {
            $allowed[$name] = true;
        }

        $enums = [];
        foreach ($allowedAttributes as $name => $type) {
            $allowed[$name] = true;
            if (str_starts_with($type, 'enum(')) {
                $enums[$name] = self::parseEnumValues($type);
            }
        }

        // Always allow css-class and mj-class
        $allowed['css-class'] = true;
        $allowed[Attributes::TAG_NAME_CLASS] = true;

        return ['allowed' => $allowed, 'enums' => $enums, 'defaults' => $defaults];
    }

    /**
     * Validate attributes against a compiled spec.
     *
     * @param AttributeSpec              $spec
     * @param array<string, string|null> $attributes
     *
     * @return string|null The first violation message, or null if all attributes are valid
     */
    public static function findViolation(array $spec, array $attributes): ?string
    {
        $unknown = array_diff_key($attributes, $spec['allowed']);
        if ([] !== $unknown) {
            return \sprintf('The option(s) "%s" do not exist.', implode('", "', array_keys($unknown)));
        }

        foreach ($attributes as $name => $value) {
            // Null and values equal to the component default are valid by construction
            if (null === $value || ($spec['defaults'][$name] ?? null) === $value) {
                continue;
            }

            if (!\is_string($value)) {
                return \sprintf('The option "%s" with value of type "%s" is invalid.', $name, get_debug_type($value));
            }

            if (isset($spec['enums'][$name]) && !\in_array($value, $spec['enums'][$name], true)) {
                return \sprintf(
                    'The option "%s" with value "%s" is invalid. Accepted values are: "%s".',
                    $name,
                    $value,
                    implode('", "', $spec['enums'][$name])
                );
            }
        }

        return null;
    }

    /**
     * @return list<string>
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
