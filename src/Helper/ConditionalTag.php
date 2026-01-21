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

final class ConditionalTag
{
    public const START_CONDITIONAL = '<!--[if mso | IE]>';
    public const END_CONDITIONAL = '<![endif]-->';

    public const START_MSO_CONDITIONAL = '<!--[if mso]>';

    public const START_NEGATION_CONDITIONAL = '<!--[if !mso | IE]><!-->';
    public const START_MSO_NEGATION = '<!--[if !mso]><!-->';
    public const END_NEGATION_CONDITIONAL = '<!--<![endif]-->';

    /**
     * Wrap content in Outlook conditional comments.
     */
    public static function wrap(string $content, bool $negation = false): string
    {
        $start = $negation ? self::START_NEGATION_CONDITIONAL : self::START_CONDITIONAL;
        $end = $negation ? self::END_NEGATION_CONDITIONAL : self::END_CONDITIONAL;

        return $start.$content.$end;
    }

    /**
     * Wrap content in MSO-only conditional comments.
     */
    public static function wrapMso(string $content, bool $negation = false): string
    {
        $start = $negation ? self::START_MSO_NEGATION : self::START_MSO_CONDITIONAL;
        $end = $negation ? self::END_NEGATION_CONDITIONAL : self::END_CONDITIONAL;

        return $start.$content.$end;
    }
}
