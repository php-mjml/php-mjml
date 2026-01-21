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

final class BorderParser
{
    /**
     * Parse a CSS border value and extract the width.
     *
     * @param string $border The border value (e.g., "1px solid red")
     *
     * @return int The border width in pixels
     */
    public static function parse(string $border): int
    {
        if ('' === $border || '0' === $border) {
            return 0;
        }

        // Match a number that is either at the start or preceded by a space
        if (preg_match('/(?:(?:^| )(\d+))/', $border, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }
}
