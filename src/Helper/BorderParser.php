<?php

declare(strict_types=1);

namespace PhpMjml\Helper;

final class BorderParser
{
    /**
     * Parse a CSS border value and extract the width.
     *
     * @param string $border The border value (e.g., "1px solid red")
     * @return int The border width in pixels
     */
    public static function parse(string $border): int
    {
        if ($border === '' || $border === '0') {
            return 0;
        }

        // Match a number that is either at the start or preceded by a space
        if (preg_match('/(?:(?:^| )(\d+))/', $border, $matches)) {
            return (int) ($matches[1] ?? 0);
        }

        return 0;
    }
}
