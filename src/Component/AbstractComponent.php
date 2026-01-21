<?php

declare(strict_types=1);

namespace PhpMjml\Component;

use PhpMjml\Renderer\RenderContext;

abstract class AbstractComponent implements ComponentInterface
{
    protected array $attributes = [];
    protected array $children = [];
    protected string $content = '';
    protected ?RenderContext $context = null;

    /**
     * Additional props passed from parent component (e.g., sibling count, index).
     */
    protected array $props = [];

    public function __construct(
        array $attributes = [],
        array $children = [],
        string $content = '',
        ?RenderContext $context = null,
        array $props = [],
    ) {
        $this->attributes = array_merge(static::getDefaultAttributes(), $attributes);
        $this->children = $children;
        $this->content = trim($content);
        $this->context = $context;
        $this->props = $props;
    }

    public static function getAllowedAttributes(): array
    {
        return [];
    }

    public static function getDefaultAttributes(): array
    {
        return [];
    }

    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? static::getDefaultAttributes()[$name] ?? null;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getContext(): ?RenderContext
    {
        return $this->context;
    }

    public function getProps(): array
    {
        return $this->props;
    }
}
