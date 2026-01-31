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

namespace PhpMjml\Components\Body;

use PhpMjml\Component\BodyComponent;

final class Raw extends BodyComponent
{
    protected static bool $endingTag = true;
    protected static bool $rawElement = true;

    public static function getComponentName(): string
    {
        return 'mj-raw';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'position' => 'enum(file-start)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getDefaultAttributes(): array
    {
        return [];
    }

    public function render(): string
    {
        return $this->getContent();
    }

    protected function shouldPreserveContent(): bool
    {
        return true;
    }
}
