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

namespace PhpMjml\Tests\Unit\Components\Body;

use PhpMjml\Component\Registry;
use PhpMjml\Components\Body\Raw;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class RawTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-raw', Raw::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertTrue(Raw::isEndingTag());
    }

    public function testIsRawElement(): void
    {
        $this->assertTrue(Raw::isRawElement());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Raw::getDefaultAttributes();

        $this->assertSame([], $defaults);
    }

    public function testAllowedAttributes(): void
    {
        $allowed = Raw::getAllowedAttributes();

        $this->assertArrayHasKey('position', $allowed);
        $this->assertSame('enum(file-start)', $allowed['position']);
    }

    public function testRenderBasicContent(): void
    {
        $raw = new Raw(
            attributes: [],
            children: [],
            content: '<div class="custom">Custom HTML</div>',
            context: $this->createContext(),
        );

        $html = $raw->render();

        $this->assertSame('<div class="custom">Custom HTML</div>', $html);
    }

    public function testRenderPassesThroughContentUnmodified(): void
    {
        $content = '<table><tr><td>Cell content</td></tr></table>';

        $raw = new Raw(
            attributes: [],
            children: [],
            content: $content,
            context: $this->createContext(),
        );

        $html = $raw->render();

        $this->assertSame($content, $html);
    }

    public function testRenderEmptyContent(): void
    {
        $raw = new Raw(
            attributes: [],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $raw->render();

        $this->assertSame('', $html);
    }

    public function testRenderHtmlWithSelfClosingTags(): void
    {
        $content = '<br/><hr/><img src="test.jpg" alt="test"/>';

        $raw = new Raw(
            attributes: [],
            children: [],
            content: $content,
            context: $this->createContext(),
        );

        $html = $raw->render();

        $this->assertSame($content, $html);
    }

    public function testRenderHtmlWithNestedElements(): void
    {
        $content = '<div><p>Nested <strong>content</strong></p></div>';

        $raw = new Raw(
            attributes: [],
            children: [],
            content: $content,
            context: $this->createContext(),
        );

        $html = $raw->render();

        $this->assertSame($content, $html);
    }

    public function testRenderHtmlWithComments(): void
    {
        $content = '<!-- This is a comment --><div>Content</div>';

        $raw = new Raw(
            attributes: [],
            children: [],
            content: $content,
            context: $this->createContext(),
        );

        $html = $raw->render();

        $this->assertSame($content, $html);
    }

    public function testRenderHtmlWithConditionalComments(): void
    {
        $content = '<!--[if mso]><table><tr><td><![endif]-->';

        $raw = new Raw(
            attributes: [],
            children: [],
            content: $content,
            context: $this->createContext(),
        );

        $html = $raw->render();

        $this->assertSame($content, $html);
    }

    public function testRenderHtmlWithNumericEntities(): void
    {
        $content = '<p>Copyright &#169; 2024 &#8212; All rights reserved</p>';

        $raw = new Raw(
            attributes: [],
            children: [],
            content: $content,
            context: $this->createContext(),
        );

        $html = $raw->render();

        $this->assertSame($content, $html);
    }

    public function testRenderHtmlWithSpecialCharactersInAttributes(): void
    {
        $content = '<a href="https://example.com?foo=1&amp;bar=2">Link</a>';

        $raw = new Raw(
            attributes: [],
            children: [],
            content: $content,
            context: $this->createContext(),
        );

        $html = $raw->render();

        $this->assertSame($content, $html);
    }

    public function testRenderHtmlWithDataAttributes(): void
    {
        $content = '<div data-custom="value" data-json=\'{"key":"value"}\'>Content</div>';

        $raw = new Raw(
            attributes: [],
            children: [],
            content: $content,
            context: $this->createContext(),
        );

        $html = $raw->render();

        $this->assertSame($content, $html);
    }

    public function testRenderHtmlWithInlineStyles(): void
    {
        $content = '<div style="color: red; font-size: 14px;">Styled content</div>';

        $raw = new Raw(
            attributes: [],
            children: [],
            content: $content,
            context: $this->createContext(),
        );

        $html = $raw->render();

        $this->assertSame($content, $html);
    }

    private function createContext(): RenderContext
    {
        return new RenderContext(
            registry: new Registry(),
            renderOptions: new RenderOptions(),
            options: ['containerWidth' => 600],
        );
    }
}
