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

final readonly class RenderOptions
{
    public function __construct(
        public bool $minify = false,
        public bool $beautify = false,
        public bool $keepComments = true,
        public bool $validationLevel = true,
    ) {
    }
}
