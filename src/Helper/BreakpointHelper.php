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

/**
 * Breakpoint calculations for responsive media queries.
 *
 * Mirrors mjml-core/lib/helpers/makeLowerBreakpoint.js.
 */
final class BreakpointHelper
{
    /**
     * Make the breakpoint 1px lower (e.g., "480px" -> "479px") so that
     * max-width media queries do not overlap the min-width ones.
     */
    public static function makeLower(string $breakpoint): string
    {
        if (1 === preg_match('/(\d+)/', $breakpoint, $matches)) {
            $pixels = (int) $matches[1] - 1;

            return $pixels.'px';
        }

        return $breakpoint;
    }
}
