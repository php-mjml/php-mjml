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
use PhpMjml\Components\Body\Divider;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class DividerTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-divider', Divider::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertTrue(Divider::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Divider::getDefaultAttributes();

        $this->assertSame('center', $defaults['align']);
        $this->assertSame('#000000', $defaults['border-color']);
        $this->assertSame('solid', $defaults['border-style']);
        $this->assertSame('4px', $defaults['border-width']);
        $this->assertSame('10px 25px', $defaults['padding']);
        $this->assertSame('100%', $defaults['width']);
    }

    public function testRenderBasic(): void
    {
        $divider = new Divider(
            attributes: [],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $divider->render();

        $this->assertStringContainsString('<p', $html);
        $this->assertStringContainsString('border-top:solid 4px #000000', $html);
        $this->assertStringContainsString('font-size:1px', $html);
        $this->assertStringContainsString('margin:0px auto', $html);
        $this->assertStringContainsString('width:100%', $html);
        // Outlook conditional
        $this->assertStringContainsString('<!--[if mso | IE]>', $html);
        $this->assertStringContainsString('<![endif]-->', $html);
    }

    public function testRenderStyled(): void
    {
        $divider = new Divider(
            attributes: [
                'border-color' => '#ff0000',
                'border-style' => 'dashed',
                'border-width' => '2px',
                'width' => '50%',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $divider->render();

        $this->assertStringContainsString('border-top:dashed 2px #ff0000', $html);
        $this->assertStringContainsString('width:50%', $html);
    }

    public function testRenderWithLeftAlign(): void
    {
        $divider = new Divider(
            attributes: ['align' => 'left'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $divider->render();

        $this->assertStringContainsString('margin:0px', $html);
        $this->assertStringContainsString('align="left"', $html);
    }

    public function testRenderWithRightAlign(): void
    {
        $divider = new Divider(
            attributes: ['align' => 'right'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $divider->render();

        $this->assertStringContainsString('margin:0px 0px 0px auto', $html);
        $this->assertStringContainsString('align="right"', $html);
    }

    public function testGetStyles(): void
    {
        $divider = new Divider(
            attributes: [
                'border-color' => '#333333',
                'border-style' => 'dotted',
                'border-width' => '1px',
                'width' => '80%',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $styles = $divider->getStyles();

        $this->assertArrayHasKey('p', $styles);
        $this->assertArrayHasKey('outlook', $styles);
        $this->assertSame('dotted 1px #333333', $styles['p']['border-top']);
        $this->assertSame('1px', $styles['p']['font-size']);
        $this->assertSame('0px auto', $styles['p']['margin']);
        $this->assertSame('80%', $styles['p']['width']);
    }

    public function testOutlookWidthCalculation(): void
    {
        $divider = new Divider(
            attributes: ['width' => '50%'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $styles = $divider->getStyles();

        // Container width is 600, padding is 50 (25+25), effective width is 550
        // 50% of 550 = 275px
        $this->assertSame('275px', $styles['outlook']['width']);
    }

    public function testOutlookWidthWithPixels(): void
    {
        $divider = new Divider(
            attributes: ['width' => '200px'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $styles = $divider->getStyles();

        $this->assertSame('200px', $styles['outlook']['width']);
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
