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

namespace PhpMjml\Security;

/**
 * Thrown when the MJML exceeds a resource limit configured in RenderOptions.
 *
 * Limits protect against inputs whose rendering cost grows much faster than
 * their size (e.g. mj-carousel, whose CSS grows with the square of its images).
 */
final class LimitExceededException extends \RuntimeException
{
    public static function create(string $limit, int $actual, int $max): self
    {
        return new self(\sprintf('MJML exceeds the %s limit: %d (maximum %d).', $limit, $actual, $max));
    }
}
