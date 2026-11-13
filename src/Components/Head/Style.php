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
    /**
     * Closing style tag sequence. CSS never needs it, but inside a <style> element it would end
     * the element and switch the browser back to HTML parsing.
     */
    private const STYLE_END_TAG_PATTERN = '/<\/(style)/i';

    private const STYLE_END_TAG_REPLACEMENT = '<\\/$1';

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
        $content = (string) preg_replace(self::STYLE_END_TAG_PATTERN, self::STYLE_END_TAG_REPLACEMENT, $this->getContent());

        if ('' === $content) {
            return;
        }

        if ('inline' === $this->getAttribute('inline')) {
            $context->globalData->inlineStyles[] = $content;
        } else {
            $context->globalData->addStyle($content);
        }
    }
}
