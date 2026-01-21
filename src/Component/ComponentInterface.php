<?php

declare(strict_types=1);

namespace PhpMjml\Component;

interface ComponentInterface
{
    public static function getComponentName(): string;

    public static function getAllowedAttributes(): array;

    public static function getDefaultAttributes(): array;

    public function getAttribute(string $name): mixed;

    public function getContent(): string;

    public function render(): string;
}
