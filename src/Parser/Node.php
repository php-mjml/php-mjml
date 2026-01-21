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

namespace PhpMjml\Parser;

final readonly class Node
{
    /**
     * @param array<string, string> $attributes
     * @param array<Node>           $children
     */
    public function __construct(
        public string $tagName,
        public array $attributes = [],
        public array $children = [],
        public string $content = '',
    ) {
    }

    public function findChild(string $tagName): ?self
    {
        foreach ($this->children as $child) {
            if ($child->tagName === $tagName) {
                return $child;
            }
        }

        return null;
    }

    /**
     * @return array<Node>
     */
    public function findChildren(string $tagName): array
    {
        return array_filter(
            $this->children,
            fn (Node $child) => $child->tagName === $tagName
        );
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }
}
