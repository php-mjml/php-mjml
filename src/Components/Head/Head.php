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

/**
 * Container for head elements (metadata, styles, fonts).
 *
 * The mj-head component is a container that holds all head-related
 * components like mj-title, mj-preview, mj-font, mj-style, etc.
 * It doesn't produce any output itself; its children are processed
 * individually by the renderer.
 */
final class Head extends HeadComponent
{
    public static function getComponentName(): string
    {
        return 'mj-head';
    }

    public function handle(RenderContext $context): void
    {
        // The mj-head component is a container only.
        // Its children are processed directly by Mjml2Html::processHead().
        // No action needed here.
    }
}
