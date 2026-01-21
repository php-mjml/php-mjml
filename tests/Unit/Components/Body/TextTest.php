<?php

declare(strict_types=1);

namespace PhpMjml\Tests\Unit\Components\Body;

use PHPUnit\Framework\TestCase;
use PhpMjml\Components\Body\Text;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PhpMjml\Component\Registry;

final class TextTest extends TestCase
{
    private function createContext(): RenderContext
    {
        return new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
        );
    }

    public function testGetComponentName(): void
    {
        $this->assertSame('mj-text', Text::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertTrue(Text::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Text::getDefaultAttributes();

        $this->assertSame('left', $defaults['align']);
        $this->assertSame('#000000', $defaults['color']);
        $this->assertSame('Ubuntu, Helvetica, Arial, sans-serif', $defaults['font-family']);
        $this->assertSame('13px', $defaults['font-size']);
        $this->assertSame('1', $defaults['line-height']);
        $this->assertSame('10px 25px', $defaults['padding']);
    }

    public function testRenderBasicText(): void
    {
        $text = new Text(
            attributes: [],
            children: [],
            content: 'Hello World',
            context: $this->createContext(),
        );

        $html = $text->render();

        $this->assertStringContainsString('<div', $html);
        $this->assertStringContainsString('Hello World', $html);
        $this->assertStringContainsString('font-family:Ubuntu, Helvetica, Arial, sans-serif', $html);
        $this->assertStringContainsString('color:#000000', $html);
    }

    public function testRenderWithCustomColor(): void
    {
        $text = new Text(
            attributes: ['color' => '#ff0000'],
            children: [],
            content: 'Red Text',
            context: $this->createContext(),
        );

        $html = $text->render();

        $this->assertStringContainsString('color:#ff0000', $html);
    }

    public function testRenderWithHeight(): void
    {
        $text = new Text(
            attributes: ['height' => '100px'],
            children: [],
            content: 'Tall text',
            context: $this->createContext(),
        );

        $html = $text->render();

        // Should contain Outlook conditional table
        $this->assertStringContainsString('<!--[if mso | IE]>', $html);
        $this->assertStringContainsString('height="100px"', $html);
        $this->assertStringContainsString('<![endif]-->', $html);
    }

    public function testGetStyles(): void
    {
        $text = new Text(
            attributes: [
                'font-family' => 'Arial',
                'font-size' => '16px',
                'color' => '#333333',
                'align' => 'center',
            ],
            children: [],
            content: 'Test',
            context: $this->createContext(),
        );

        $styles = $text->getStyles();

        $this->assertArrayHasKey('text', $styles);
        $this->assertSame('Arial', $styles['text']['font-family']);
        $this->assertSame('16px', $styles['text']['font-size']);
        $this->assertSame('#333333', $styles['text']['color']);
        $this->assertSame('center', $styles['text']['text-align']);
    }
}
