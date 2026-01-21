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

namespace PhpMjml\Component;

use PhpMjml\Renderer\RenderContext;

abstract class HeadComponent extends AbstractComponent
{
    /**
     * Process the head component and update the context.
     */
    abstract public function handle(RenderContext $context): void;

    public function render(): string
    {
        return '';
    }
}
