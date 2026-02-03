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

final class Registry
{
    /**
     * Default ending tags used when Registry is not available.
     *
     * @see https://documentation.mjml.io/#ending-tags
     */
    public const DEFAULT_ENDING_TAGS = [
        'mj-accordion-text',
        'mj-accordion-title',
        'mj-button',
        'mj-navbar-link',
        'mj-raw',
        'mj-social-element',
        'mj-style',
        'mj-table',
        'mj-text',
    ];

    /** @var array<string, class-string<ComponentInterface>> */
    private array $components = [];

    /**
     * @param class-string<ComponentInterface> $componentClass
     */
    public function register(string $componentClass): void
    {
        $name = $componentClass::getComponentName();
        $this->components[$name] = $componentClass;
    }

    /**
     * @param array<class-string<ComponentInterface>> $componentClasses
     */
    public function registerMany(array $componentClasses): void
    {
        foreach ($componentClasses as $componentClass) {
            $this->register($componentClass);
        }
    }

    public function has(string $name): bool
    {
        return isset($this->components[$name]);
    }

    /**
     * @return class-string<ComponentInterface>|null
     */
    public function get(string $name): ?string
    {
        return $this->components[$name] ?? null;
    }

    /**
     * @return array<string, class-string<ComponentInterface>>
     */
    public function all(): array
    {
        return $this->components;
    }

    /**
     * Get tag names of all registered ending tag components.
     *
     * Ending tags are components that contain text/HTML content instead of
     * other MJML tags. The content remains unprocessed by the MJML engine.
     *
     * @return list<string>
     *
     * @see https://documentation.mjml.io/#ending-tags
     */
    public function getEndingTagNames(): array
    {
        $endingTags = [];

        foreach ($this->components as $name => $componentClass) {
            if ($componentClass::isEndingTag()) {
                $endingTags[] = $name;
            }
        }

        return $endingTags;
    }
}
