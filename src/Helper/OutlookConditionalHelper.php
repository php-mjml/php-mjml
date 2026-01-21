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

final class OutlookConditionalHelper
{
    /**
     * Merge adjacent MSO conditionals in the HTML output.
     *
     * This function removes the pattern `<![endif]--><!--[if mso | IE]>`
     * to merge adjacent conditional blocks into a single block.
     */
    public static function mergeConditionals(string $content): string
    {
        return (string) preg_replace(
            '/<!\[endif\]-->\s*<!--\[if mso \| IE\]>/',
            '',
            $content
        );
    }
}
