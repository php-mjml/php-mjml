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
}
