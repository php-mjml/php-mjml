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

final class Title extends HeadComponent
{
    public static function getComponentName(): string
    {
        return 'mj-title';
    }

    public function handle(RenderContext $context): void
    {
        $context->title = $this->getContent();
    }
}
