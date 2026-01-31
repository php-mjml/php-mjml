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
use Symfony\Component\OptionsResolver\OptionsResolver;

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
    /** @var array<string, OptionsResolver> */
    private static array $resolverCache = [];

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
        $this->attributes = $this->resolveAttributes($attributes);
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
        return $this->attributes[$name] ?? null;
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
     * Resolve and validate attributes using OptionsResolver.
     *
     * Merges attributes from various sources with proper priority:
     * 1. Component default attributes (lowest priority)
     * 2. mj-all attributes (from mj-attributes)
     * 3. Component-specific default attributes (from mj-attributes)
     * 4. mj-class attributes (from mj-attributes)
     * 5. Inherited attributes from parent component
     * 6. Instance attributes passed to constructor (highest priority)
     *
     * @param array<string, string|null> $instanceAttributes
     *
     * @return array<string, string|null>
     */
    private function resolveAttributes(array $instanceAttributes): array
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

            // Apply mj-class attributes
            $mjClassAttr = $instanceAttributes['mj-class'] ?? null;
            if (null !== $mjClassAttr && '' !== $mjClassAttr) {
                $classNames = preg_split('/\s+/', $mjClassAttr, -1, \PREG_SPLIT_NO_EMPTY);
                if (false !== $classNames) {
                    $existingCssClass = $merged['css-class'] ?? '';
                    foreach ($classNames as $className) {
                        $classAttributes = $this->context->headAttributes['mj-class'][$className] ?? [];
                        if ([] !== $classAttributes) {
                            // Handle css-class merging (multiple classes get concatenated)
                            if (isset($classAttributes['css-class']) && '' !== $existingCssClass) {
                                $classAttributes['css-class'] = $existingCssClass.' '.$classAttributes['css-class'];
                            }
                            $merged = array_merge($merged, $classAttributes);
                            $existingCssClass = $merged['css-class'] ?? '';
                        }
                    }
                }
            }

            // Apply inherited attributes from parent component
            $inheritedAttributes = $this->context->inheritedAttributes;
            if ([] !== $inheritedAttributes) {
                $merged = array_merge($merged, $inheritedAttributes);
            }
        }

        // Instance attributes have highest priority (excluding mj-class which was already processed)
        $instanceAttributesWithoutMjClass = array_filter(
            $instanceAttributes,
            static fn (string $key) => 'mj-class' !== $key,
            \ARRAY_FILTER_USE_KEY
        );

        $merged = array_merge($merged, $instanceAttributesWithoutMjClass);

        // Validate through cached OptionsResolver
        return $this->validateAttributes($merged);
    }

    /**
     * Validate attributes using a cached OptionsResolver.
     *
     * @param array<string, string|null> $attributes
     *
     * @return array<string, string|null>
     */
    private function validateAttributes(array $attributes): array
    {
        $class = static::class;

        if (!isset(self::$resolverCache[$class])) {
            self::$resolverCache[$class] = AttributeResolver::createResolver(
                static::getAllowedAttributes(),
                static::getDefaultAttributes()
            );
        }

        try {
            return self::$resolverCache[$class]->resolve($attributes);
        } catch (\Symfony\Component\OptionsResolver\Exception\ExceptionInterface) {
            // On validation failure, return unvalidated for backward compatibility
            // This can happen with dynamic attributes or edge cases
            return $attributes;
        }
    }
}
