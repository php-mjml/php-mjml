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
use PhpMjml\Components\Body\Button;
use PhpMjml\Components\Body\Column;
use PhpMjml\Components\Body\Divider;
use PhpMjml\Components\Body\Hero;
use PhpMjml\Components\Body\Image;
use PhpMjml\Components\Body\Raw;
use PhpMjml\Components\Body\Section;
use PhpMjml\Components\Body\Social;
use PhpMjml\Components\Body\SocialElement;
use PhpMjml\Components\Body\Spacer;
use PhpMjml\Components\Body\Text;
use PhpMjml\Components\Head\Attributes;
use PhpMjml\Components\Head\Breakpoint;
use PhpMjml\Components\Head\Font;
use PhpMjml\Components\Head\Head;
use PhpMjml\Components\Head\HtmlAttributes;
use PhpMjml\Components\Head\Preview;
use PhpMjml\Components\Head\Style;
use PhpMjml\Components\Head\Title;

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
            Button::class,
            Column::class,
            Divider::class,
            Hero::class,
            Image::class,
            Raw::class,
            Section::class,
            Social::class,
            SocialElement::class,
            Spacer::class,
            Text::class,

            // Head components
            Attributes::class,
            Breakpoint::class,
            Font::class,
            Head::class,
            HtmlAttributes::class,
            Preview::class,
            Style::class,
            Title::class,
        ];
    }
}
