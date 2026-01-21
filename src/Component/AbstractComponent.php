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

abstract class AbstractComponent implements ComponentInterface
{
    /** @var array<string, string|null> */
    protected array $attributes = [];

    /** @var array<ComponentInterface> */
    protected array $children = [];

    protected string $content = '';
    protected ?RenderContext $context = null;

    /**
     * Additional props passed from parent component (e.g., sibling count, index).
     *
     * @var array<string, mixed>
     */
    protected array $props = [];

    /**
     * @param array<string, string|null> $attributes
     * @param array<ComponentInterface>  $children
     * @param array<string, mixed>       $props
     */
    public function __construct(
        array $attributes = [],
        array $children = [],
        string $content = '',
        ?RenderContext $context = null,
        array $props = [],
    ) {
        $this->context = $context;
        $this->attributes = $this->mergeAttributes($attributes);
        $this->children = $children;
        $this->content = trim($content);
        $this->props = $props;
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [];
    }

    /**
     * @return array<string, string|null>
     */
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

    /**
     * @return array<ComponentInterface>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function getContext(): ?RenderContext
    {
        return $this->context;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProps(): array
    {
        return $this->props;
    }

    /**
     * Merge attributes from various sources.
     *
     * Priority (lowest to highest):
     * 1. Component default attributes
     * 2. mj-all attributes (from mj-attributes)
     * 3. Component-specific default attributes (from mj-attributes)
     * 4. Instance attributes passed to constructor
     *
     * @param array<string, string|null> $instanceAttributes
     *
     * @return array<string, string|null>
     */
    private function mergeAttributes(array $instanceAttributes): array
    {
        $merged = static::getDefaultAttributes();

        if (null !== $this->context) {
            // Apply mj-all defaults
            $mjAllDefaults = $this->context->headAttributes['mj-all'] ?? [];
            if ([] !== $mjAllDefaults) {
                $merged = array_merge($merged, $mjAllDefaults);
            }

            // Apply component-specific defaults (e.g., mj-text defaults)
            $componentName = static::getComponentName();
            $componentDefaults = $this->context->headAttributes[$componentName] ?? [];
            if ([] !== $componentDefaults) {
                $merged = array_merge($merged, $componentDefaults);
            }
        }

        // Instance attributes have highest priority
        return array_merge($merged, $instanceAttributes);
    }
}
