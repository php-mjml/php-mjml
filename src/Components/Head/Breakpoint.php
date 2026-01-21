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

namespace PhpMjml\Components\Head;

use PhpMjml\Component\HeadComponent;
use PhpMjml\Renderer\RenderContext;

final class Breakpoint extends HeadComponent
{
    public static function getComponentName(): string
    {
        return 'mj-breakpoint';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'width' => 'unit(px)',
        ];
    }

    public function handle(RenderContext $context): void
    {
        $width = $this->getAttribute('width');

        if (null !== $width) {
            $context->breakpoint = $width;
        }
    }
}
