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
    public const DEFAULT_FONTS = [
        'Open Sans' => 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,700',
        'Droid Sans' => 'https://fonts.googleapis.com/css?family=Droid+Sans:300,400,500,700',
        'Lato' => 'https://fonts.googleapis.com/css?family=Lato:300,400,500,700',
        'Roboto' => 'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700',
        'Ubuntu' => 'https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700',
    ];

    /**
     * @param array<string, string> $fonts Font URLs indexed by font name
     */
    public function __construct(
        public bool $minify = false,
        public bool $beautify = false,
        public bool $keepComments = true,
        public bool $validationLevel = true,
        public array $fonts = self::DEFAULT_FONTS,
    ) {
    }
}
