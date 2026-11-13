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

namespace PhpMjml\Helper;

final class WidthParser
{
    private const UNIT_REGEX = '/[\d.,]*(\D*)$/';

    /**
     * A unit is only ever letters or a percent sign. The parsed unit ends up in
     * generated CSS (media queries, class names), so anything else is discarded.
     */
    private const VALID_UNIT_REGEX = '/^(?:[a-z]+|%)$/i';

    private const DEFAULT_UNIT = 'px';

    /**
     * Parse a width value and return its numeric value and unit.
     *
     * @return array{parsedWidth: float|int, unit: string}
     */
    public static function parse(string $width, bool $parseFloatToInt = true): array
    {
        preg_match(self::UNIT_REGEX, $width, $matches);
        $unit = $matches[1] ?? self::DEFAULT_UNIT;

        if (1 !== preg_match(self::VALID_UNIT_REGEX, $unit)) {
            $unit = self::DEFAULT_UNIT;
        }

        $parsedWidth = match ($unit) {
            '%' => $parseFloatToInt ? (int) $width : (float) $width,
            'px' => (int) $width,
            default => (int) $width,
        };

        return [
            'parsedWidth' => $parsedWidth,
            'unit' => $unit,
        ];
    }
}
