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

final class Style extends HeadComponent
{
    public static function getComponentName(): string
    {
        return 'mj-style';
    }

    public static function isEndingTag(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'inline' => 'string',
        ];
    }

    public function handle(RenderContext $context): void
    {
        $content = $this->getContent();

        if ('' === $content) {
            return;
        }

        if ('inline' === $this->getAttribute('inline')) {
            $context->globalData->inlineStyles[] = $content;
        } else {
            $context->globalData->addComponentHeadStyle($content);
        }
    }
}
