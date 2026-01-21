<?php

declare(strict_types=1);

namespace PhpMjml\Renderer;

final readonly class RenderOptions
{
    public function __construct(
        public bool $minify = false,
        public bool $beautify = false,
        public bool $keepComments = true,
        public bool $validationLevel = true,
    ) {}
}
