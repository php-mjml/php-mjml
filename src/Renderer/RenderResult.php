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

namespace PhpMjml\Renderer;

final readonly class RenderResult
{
    /**
     * @param array<int, string> $errors List of error messages
     */
    public function __construct(
        public string $html,
        public array $errors = [],
    ) {
    }

    public function hasErrors(): bool
    {
        return [] !== $this->errors;
    }
}
