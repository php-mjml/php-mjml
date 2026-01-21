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

namespace PhpMjml\Components\Head;

use PhpMjml\Component\HeadComponent;
use PhpMjml\Renderer\RenderContext;

final class Font extends HeadComponent
{
    public static function getComponentName(): string
    {
        return 'mj-font';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'name' => 'string',
            'href' => 'string',
        ];
    }

    public function handle(RenderContext $context): void
    {
        $name = $this->getAttribute('name');
        $href = $this->getAttribute('href');

        if (null !== $name && null !== $href) {
            $context->fonts[$name] = $href;
        }
    }
}
