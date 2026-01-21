<?php

declare(strict_types=1);

namespace PhpMjml\Helper;

final class CssHelper
{
    /**
     * Suffix CSS classes with a given suffix.
     *
     * @param string|null $classes Space-separated CSS classes
     * @param string $suffix Suffix to append to each class
     * @return string Suffixed classes or empty string
     */
    public static function suffixCssClasses(?string $classes, string $suffix): string
    {
        if ($classes === null || $classes === '') {
            return '';
        }

        $classList = preg_split('/\s+/', trim($classes));
        $suffixed = array_map(fn($c) => "{$c}-{$suffix}", $classList);

        return implode(' ', $suffixed);
    }
}
