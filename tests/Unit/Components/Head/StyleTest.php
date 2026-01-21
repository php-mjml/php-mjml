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

namespace PhpMjml\Tests\Unit\Components\Head;

use PhpMjml\Component\Registry;
use PhpMjml\Components\Head\Style;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class StyleTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-style', Style::getComponentName());
    }

    public function testHandleAddsStyleToComponentHeadStyle(): void
    {
        $context = $this->createContext();
        $cssContent = '.test { color: red; }';

        $style = new Style(
            attributes: [],
            children: [],
            content: $cssContent,
            context: $context,
        );

        $style->handle($context);

        $this->assertContains($cssContent, $context->globalData->componentHeadStyle);
    }

    public function testHandleWithInlineAttributeAddsToInlineStyles(): void
    {
        $context = $this->createContext();
        $cssContent = '.test { color: blue; }';

        $style = new Style(
            attributes: ['inline' => 'inline'],
            children: [],
            content: $cssContent,
            context: $context,
        );

        $style->handle($context);

        $this->assertContains($cssContent, $context->globalData->inlineStyles);
        $this->assertNotContains($cssContent, $context->globalData->componentHeadStyle);
    }

    public function testHandleWithEmptyContentDoesNotAddStyle(): void
    {
        $context = $this->createContext();

        $style = new Style(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $style->handle($context);

        $this->assertSame([], $context->globalData->componentHeadStyle);
    }

    public function testRenderReturnsEmptyString(): void
    {
        $style = new Style(
            attributes: [],
            children: [],
            content: '.test { color: red; }',
            context: $this->createContext(),
        );

        $this->assertSame('', $style->render());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Style::getDefaultAttributes();

        $this->assertSame([], $defaults);
    }

    public function testAllowedAttributes(): void
    {
        $allowed = Style::getAllowedAttributes();

        $this->assertSame(['inline' => 'string'], $allowed);
    }

    private function createContext(): RenderContext
    {
        return new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
        );
    }
}
