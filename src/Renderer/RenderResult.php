<?php

declare(strict_types=1);

namespace PhpMjml\Renderer;

final readonly class RenderResult
{
    public function __construct(
        public string $html,
        public array $errors = [],
    ) {}

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }
}
