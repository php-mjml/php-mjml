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

final class CssHelper
{
    /**
     * Suffix CSS classes with a given suffix.
     *
     * @param string|null $classes Space-separated CSS classes
     * @param string      $suffix  Suffix to append to each class
     *
     * @return string Suffixed classes or empty string
     */
    public static function suffixCssClasses(?string $classes, string $suffix): string
    {
        if (null === $classes || '' === $classes) {
            return '';
        }

        $classList = preg_split('/\s+/', trim($classes));
        if (false === $classList) {
            return '';
        }

        /** @var list<string> $suffixed */
        $suffixed = array_map(static fn (string $c): string => "{$c}-{$suffix}", $classList);

        return implode(' ', $suffixed);
    }
}
