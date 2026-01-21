<?php

declare(strict_types=1);

namespace PhpMjml\Parser;

final readonly class Node
{
    /**
     * @param array<string, string> $attributes
     * @param array<Node> $children
     */
    public function __construct(
        public string $tagName,
        public array $attributes = [],
        public array $children = [],
        public string $content = '',
    ) {}

    public function findChild(string $tagName): ?Node
    {
        foreach ($this->children as $child) {
            if ($child->tagName === $tagName) {
                return $child;
            }
        }

        return null;
    }

    public function findChildren(string $tagName): array
    {
        return array_filter(
            $this->children,
            fn(Node $child) => $child->tagName === $tagName
        );
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }
}
