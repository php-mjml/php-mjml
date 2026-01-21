<?php

declare(strict_types=1);

namespace PhpMjml\Helper;

final class ShorthandParser
{
    private const DIRECTION_TOP = 'top';
    private const DIRECTION_RIGHT = 'right';
    private const DIRECTION_BOTTOM = 'bottom';
    private const DIRECTION_LEFT = 'left';

    /**
     * Parse a CSS shorthand value (like padding/margin) and return the value for a specific direction.
     *
     * @param string $cssValue The shorthand CSS value (e.g., "10px 20px" or "10px 20px 30px 40px")
     * @param string $direction The direction to get ('top', 'right', 'bottom', 'left')
     */
    public static function parse(string $cssValue, string $direction): int
    {
        $values = preg_split('/\s+/', trim($cssValue));
        $values = array_filter($values, fn($v) => $v !== '');
        $values = array_values($values);

        $count = count($values);

        if ($count === 0) {
            return 0;
        }

        $directions = match ($count) {
            1 => [
                self::DIRECTION_TOP => 0,
                self::DIRECTION_RIGHT => 0,
                self::DIRECTION_BOTTOM => 0,
                self::DIRECTION_LEFT => 0,
            ],
            2 => [
                self::DIRECTION_TOP => 0,
                self::DIRECTION_BOTTOM => 0,
                self::DIRECTION_LEFT => 1,
                self::DIRECTION_RIGHT => 1,
            ],
            3 => [
                self::DIRECTION_TOP => 0,
                self::DIRECTION_LEFT => 1,
                self::DIRECTION_RIGHT => 1,
                self::DIRECTION_BOTTOM => 2,
            ],
            default => [
                self::DIRECTION_TOP => 0,
                self::DIRECTION_RIGHT => 1,
                self::DIRECTION_BOTTOM => 2,
                self::DIRECTION_LEFT => 3,
            ],
        };

        $index = $directions[$direction] ?? 0;

        return (int) ($values[$index] ?? 0);
    }
}
