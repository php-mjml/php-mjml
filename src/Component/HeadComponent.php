<?php

declare(strict_types=1);

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
