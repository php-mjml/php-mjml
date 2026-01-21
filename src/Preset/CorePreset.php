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

namespace PhpMjml\Preset;

use PhpMjml\Components\Body\Body;
use PhpMjml\Components\Body\Column;
use PhpMjml\Components\Body\Image;
use PhpMjml\Components\Body\Section;
use PhpMjml\Components\Body\Text;

final class CorePreset
{
    /**
     * @return array<int, class-string<\PhpMjml\Component\ComponentInterface>>
     */
    public static function getComponents(): array
    {
        return [
            // Body components
            Body::class,
            Section::class,
            Column::class,
            Image::class,
            Text::class,
        ];
    }
}
