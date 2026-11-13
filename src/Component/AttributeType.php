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

/**
 * Validates attribute values against MJML attribute types.
 *
 * Port of mjml-core/src/types, with one deliberate difference: every matcher is
 * anchored. The JS rgb()/rgba()/integer matchers are not, which lets values such as
 * 'rgb(0,0,0)"><script>' pass validation.
 *
 * Supported types:
 * - 'string'                                      any value
 * - 'color'                                       hex, rgb(), rgba() or a named color
 * - 'boolean'                                     true or false
 * - 'integer'                                     digits, optionally followed by a unit suffix
 * - 'enum(a,b,c)'                                 one of the listed values (a trailing comma allows '')
 * - 'unit(px,%)', 'unit(px,%){1,4}'               number with one of the units, 1 to n values
 * - 'unitWithNegative(px,em)'                     same, negative numbers allowed
 *
 * Unknown types are treated as valid so a custom component never fails validation
 * because of a type this class does not know.
 */
final class AttributeType
{
    public const STRING = 'string';
    public const COLOR = 'color';
    public const BOOLEAN = 'boolean';
    public const INTEGER = 'integer';

    private const ENUM_PATTERN = '/^enum\((.*)\)$/';
    private const UNIT_PATTERN = '/^(unit|unitWithNegative)\(([^)]*)\)(?:\{([^}]+)\})?$/';
    private const UNIT_WITH_NEGATIVE = 'unitWithNegative';
    private const UNIT_AUTO = 'auto';

    private const BOOLEAN_PATTERN = '/^(true|false)$/i';
    /**
     * Digits, optionally followed by a unit suffix: the JS matcher (/\d+/) is unanchored and
     * accepts values like cellpadding="6px", which real templates use.
     */
    private const INTEGER_PATTERN = '/^\d+[a-z%]*$/i';

    /** @var list<string> */
    private const COLOR_PATTERNS = [
        '/^rgba\(\d{1,3},\s?\d{1,3},\s?\d{1,3},\s?\d(\.\d{1,3})?\)$/i',
        '/^rgb\(\d{1,3},\s?\d{1,3},\s?\d{1,3}\)$/i',
        '/^#([0-9a-f]{3}){1,2}$/i',
    ];

    /**
     * Named colors accepted by MJML (mjml-core/src/types/helpers/colors.js).
     *
     * @var list<string>
     */
    private const COLOR_NAMES = [
        'aliceblue', 'antiquewhite', 'aqua', 'aquamarine', 'azure', 'beige', 'bisque', 'black', 'blanchedalmond',
        'blue', 'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse', 'chocolate', 'coral',
        'cornflowerblue', 'cornsilk', 'crimson', 'cyan', 'darkblue', 'darkcyan', 'darkgoldenrod', 'darkgray',
        'darkgreen', 'darkgrey', 'darkkhaki', 'darkmagenta', 'darkolivegreen', 'darkorange', 'darkorchid',
        'darkred', 'darksalmon', 'darkseagreen', 'darkslateblue', 'darkslategray', 'darkslategrey',
        'darkturquoise', 'darkviolet', 'deeppink', 'deepskyblue', 'dimgray', 'dimgrey', 'dodgerblue', 'firebrick',
        'floralwhite', 'forestgreen', 'fuchsia', 'gainsboro', 'ghostwhite', 'gold', 'goldenrod', 'gray', 'green',
        'greenyellow', 'grey', 'honeydew', 'hotpink', 'indianred', 'indigo', 'inherit', 'ivory', 'khaki',
        'lavender', 'lavenderblush', 'lawngreen', 'lemonchiffon', 'lightblue', 'lightcoral', 'lightcyan',
        'lightgoldenrodyellow', 'lightgray', 'lightgreen', 'lightgrey', 'lightpink', 'lightsalmon',
        'lightseagreen', 'lightskyblue', 'lightslategray', 'lightslategrey', 'lightsteelblue', 'lightyellow',
        'lime', 'limegreen', 'linen', 'magenta', 'maroon', 'mediumaquamarine', 'mediumblue', 'mediumorchid',
        'mediumpurple', 'mediumseagreen', 'mediumslateblue', 'mediumspringgreen', 'mediumturquoise',
        'mediumvioletred', 'midnightblue', 'mintcream', 'mistyrose', 'moccasin', 'navajowhite', 'navy', 'oldlace',
        'olive', 'olivedrab', 'orange', 'orangered', 'orchid', 'palegoldenrod', 'palegreen', 'paleturquoise',
        'palevioletred', 'papayawhip', 'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'purple',
        'rebeccapurple', 'red', 'rosybrown', 'royalblue', 'saddlebrown', 'salmon', 'sandybrown', 'seagreen',
        'seashell', 'sienna', 'silver', 'skyblue', 'slateblue', 'slategray', 'slategrey', 'snow', 'springgreen',
        'steelblue', 'tan', 'teal', 'thistle', 'tomato', 'transparent', 'turquoise', 'violet', 'wheat', 'white',
        'whitesmoke', 'yellow', 'yellowgreen',
    ];

    /**
     * Compiled unit matchers, keyed by type string.
     *
     * @var array<string, string>
     */
    private static array $unitPatternCache = [];

    public static function isValid(string $type, string $value): bool
    {
        // An empty value is "unset": it renders nothing and falls back to defaults
        if ('' === $value) {
            return true;
        }

        if (self::STRING === $type) {
            return true;
        }

        if (self::COLOR === $type) {
            return self::isValidColor($value);
        }

        if (self::BOOLEAN === $type) {
            return 1 === preg_match(self::BOOLEAN_PATTERN, $value);
        }

        if (self::INTEGER === $type) {
            return 1 === preg_match(self::INTEGER_PATTERN, $value);
        }

        if (1 === preg_match(self::ENUM_PATTERN, $type, $matches)) {
            return \in_array($value, explode(',', $matches[1]), true);
        }

        $unitPattern = self::getUnitPattern($type);
        if (null !== $unitPattern) {
            return 1 === preg_match($unitPattern, $value);
        }

        return true;
    }

    private static function isValidColor(string $value): bool
    {
        if (\in_array($value, self::COLOR_NAMES, true)) {
            return true;
        }

        foreach (self::COLOR_PATTERNS as $pattern) {
            if (1 === preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build the matcher for a unit type, mirroring mjml-core/src/types/unit.js.
     */
    private static function getUnitPattern(string $type): ?string
    {
        if (isset(self::$unitPatternCache[$type])) {
            return self::$unitPatternCache[$type];
        }

        if (1 !== preg_match(self::UNIT_PATTERN, $type, $matches)) {
            return null;
        }

        $allowNegative = self::UNIT_WITH_NEGATIVE === $matches[1] ? '-|' : '';
        $units = explode(',', $matches[2]);
        $allowAuto = \in_array(self::UNIT_AUTO, $units, true) ? '|'.self::UNIT_AUTO : '';
        $unitAlternatives = implode('|', array_map(
            static fn (string $unit): string => preg_quote($unit, '/'),
            array_filter($units, static fn (string $unit): bool => self::UNIT_AUTO !== $unit),
        ));

        // Quantifier such as {1,4}; defaults to exactly one value
        $count = $matches[3] ?? '1';
        if (1 !== preg_match('/^\d+(,\d+)?$/', $count)) {
            return null;
        }

        return self::$unitPatternCache[$type] = \sprintf(
            '/^(((%s\d|,|\.)+(%s)|0%s)( )?){%s}$/',
            $allowNegative,
            $unitAlternatives,
            $allowAuto,
            $count
        );
    }
}
