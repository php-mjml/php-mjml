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
 * Generates random hex strings for component IDs (e.g., carousel, navbar).
 *
 * Mirrors mjml-core/lib/helpers/genRandomHexString.js.
 */
final class RandomHexString
{
    public static function generate(int $length): string
    {
        $byteLength = max(1, (int) ($length / 2));

        return bin2hex(random_bytes($byteLength));
    }
}
