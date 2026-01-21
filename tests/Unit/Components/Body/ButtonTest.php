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
use PhpMjml\Components\Body\Button;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class ButtonTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-button', Button::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertTrue(Button::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Button::getDefaultAttributes();

        $this->assertSame('center', $defaults['align']);
        $this->assertSame('#414141', $defaults['background-color']);
        $this->assertSame('none', $defaults['border']);
        $this->assertSame('3px', $defaults['border-radius']);
        $this->assertSame('#ffffff', $defaults['color']);
        $this->assertSame('Ubuntu, Helvetica, Arial, sans-serif', $defaults['font-family']);
        $this->assertSame('13px', $defaults['font-size']);
        $this->assertSame('normal', $defaults['font-weight']);
        $this->assertSame('10px 25px', $defaults['inner-padding']);
        $this->assertSame('120%', $defaults['line-height']);
        $this->assertSame('10px 25px', $defaults['padding']);
        $this->assertSame('_blank', $defaults['target']);
        $this->assertSame('none', $defaults['text-decoration']);
        $this->assertSame('none', $defaults['text-transform']);
        $this->assertSame('middle', $defaults['vertical-align']);
    }

    public function testRenderBasicButton(): void
    {
        $button = new Button(
            attributes: [],
            children: [],
            content: 'Click me',
            context: $this->createContext(),
        );

        $html = $button->render();

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('<p', $html);
        $this->assertStringContainsString('Click me', $html);
        $this->assertStringContainsString('background:#414141', $html);
        $this->assertStringContainsString('color:#ffffff', $html);
        $this->assertStringContainsString('role="presentation"', $html);
    }

    public function testRenderButtonWithHref(): void
    {
        $button = new Button(
            attributes: ['href' => 'https://example.com'],
            children: [],
            content: 'Visit Site',
            context: $this->createContext(),
        );

        $html = $button->render();

        $this->assertStringContainsString('<a', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('Visit Site', $html);
    }

    public function testRenderButtonWithCustomColors(): void
    {
        $button = new Button(
            attributes: [
                'background-color' => '#ff0000',
                'color' => '#000000',
            ],
            children: [],
            content: 'Red Button',
            context: $this->createContext(),
        );

        $html = $button->render();

        $this->assertStringContainsString('background:#ff0000', $html);
        $this->assertStringContainsString('color:#000000', $html);
        $this->assertStringContainsString('bgcolor="#ff0000"', $html);
    }

    public function testRenderButtonWithNoneBackgroundColor(): void
    {
        $button = new Button(
            attributes: ['background-color' => 'none'],
            children: [],
            content: 'Transparent Button',
            context: $this->createContext(),
        );

        $html = $button->render();

        // Should not have bgcolor attribute when background is 'none'
        $this->assertStringNotContainsString('bgcolor=', $html);
    }

    public function testRenderButtonWithBorderRadius(): void
    {
        $button = new Button(
            attributes: ['border-radius' => '10px'],
            children: [],
            content: 'Rounded Button',
            context: $this->createContext(),
        );

        $html = $button->render();

        $this->assertStringContainsString('border-radius:10px', $html);
    }

    public function testGetStyles(): void
    {
        $button = new Button(
            attributes: [
                'background-color' => '#007bff',
                'color' => '#ffffff',
                'font-family' => 'Arial',
                'font-size' => '16px',
                'border-radius' => '5px',
            ],
            children: [],
            content: 'Test',
            context: $this->createContext(),
        );

        $styles = $button->getStyles();

        $this->assertArrayHasKey('table', $styles);
        $this->assertArrayHasKey('td', $styles);
        $this->assertArrayHasKey('content', $styles);

        $this->assertSame('#007bff', $styles['td']['background']);
        $this->assertSame('#007bff', $styles['content']['background']);
        $this->assertSame('#ffffff', $styles['content']['color']);
        $this->assertSame('Arial', $styles['content']['font-family']);
        $this->assertSame('16px', $styles['content']['font-size']);
        $this->assertSame('5px', $styles['content']['border-radius']);
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
