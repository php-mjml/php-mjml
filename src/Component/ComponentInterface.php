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

interface ComponentInterface
{
    public static function getComponentName(): string;

    /**
     * Returns the allowed attributes with their validation rules.
     *
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array;

    /**
     * Returns the default attribute values.
     *
     * @return array<string, string|null>
     */
    public static function getDefaultAttributes(): array;

    public function getAttribute(string $name): mixed;

    public function getContent(): string;

    public function render(): string;
}
