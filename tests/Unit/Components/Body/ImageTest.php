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
use PhpMjml\Components\Body\Image;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class ImageTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-image', Image::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertTrue(Image::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Image::getDefaultAttributes();

        $this->assertSame('', $defaults['alt']);
        $this->assertSame('center', $defaults['align']);
        $this->assertSame('0', $defaults['border']);
        $this->assertSame('auto', $defaults['height']);
        $this->assertSame('10px 25px', $defaults['padding']);
        $this->assertSame('_blank', $defaults['target']);
        $this->assertSame('13px', $defaults['font-size']);
    }

    public function testRenderBasicImage(): void
    {
        $image = new Image(
            attributes: ['src' => 'https://example.com/image.jpg'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $image->render();

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString('src="https://example.com/image.jpg"', $html);
        $this->assertStringContainsString('role="presentation"', $html);
    }

    public function testRenderWithAlt(): void
    {
        $image = new Image(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'alt' => 'A beautiful image',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $image->render();

        $this->assertStringContainsString('alt="A beautiful image"', $html);
    }

    public function testRenderWithLink(): void
    {
        $image = new Image(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'href' => 'https://example.com',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $image->render();

        $this->assertStringContainsString('<a', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testRenderWithCustomTarget(): void
    {
        $image = new Image(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'href' => 'https://example.com',
                'target' => '_self',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $image->render();

        $this->assertStringContainsString('target="_self"', $html);
    }

    public function testRenderWithBorder(): void
    {
        $image = new Image(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'border' => '1px solid #000',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $image->render();

        $this->assertStringContainsString('border:1px solid #000', $html);
    }

    public function testRenderWithBorderRadius(): void
    {
        $image = new Image(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'border-radius' => '10px',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $image->render();

        $this->assertStringContainsString('border-radius:10px', $html);
    }

    public function testRenderWithExplicitHeight(): void
    {
        $image = new Image(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'height' => '100px',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $image->render();

        $this->assertStringContainsString('height="100"', $html);
        $this->assertStringContainsString('height:100px', $html);
    }

    public function testRenderWithAutoHeight(): void
    {
        $image = new Image(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'height' => 'auto',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $image->render();

        $this->assertStringContainsString('height="auto"', $html);
    }

    public function testRenderWithFluidOnMobile(): void
    {
        $context = $this->createContext();
        $image = new Image(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'fluid-on-mobile' => 'true',
            ],
            children: [],
            content: '',
            context: $context,
        );

        $html = $image->render();

        $this->assertStringContainsString('class="mj-full-width-mobile"', $html);
        $styles = $context->getStyles();
        $this->assertCount(1, $styles);
        $this->assertStringContainsString('@media only screen and (max-width:479px)', $styles[0]);
        $this->assertStringContainsString('table.mj-full-width-mobile { width: 100% !important; }', $styles[0]);
    }

    public function testRenderWithWidth(): void
    {
        $image = new Image(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'width' => '300px',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $image->render();

        $this->assertStringContainsString('width="300"', $html);
    }

    public function testGetStyles(): void
    {
        $image = new Image(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'border' => '1px solid red',
                'border-radius' => '5px',
                'height' => '100px',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $styles = $image->getStyles();

        $this->assertArrayHasKey('img', $styles);
        $this->assertArrayHasKey('td', $styles);
        $this->assertArrayHasKey('table', $styles);

        $this->assertSame('1px solid red', $styles['img']['border']);
        $this->assertSame('5px', $styles['img']['border-radius']);
        $this->assertSame('100px', $styles['img']['height']);
        $this->assertSame('block', $styles['img']['display']);
        $this->assertSame('100%', $styles['img']['width']);
        $this->assertSame('none', $styles['img']['outline']);
        $this->assertSame('none', $styles['img']['text-decoration']);
    }

    public function testContentWidthRespectsContainerWidth(): void
    {
        $context = $this->createContext(containerWidth: 400);

        $image = new Image(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'width' => '600px',
            ],
            children: [],
            content: '',
            context: $context,
        );

        $html = $image->render();

        // Width should be capped at container width (400) minus padding (50)
        $this->assertStringContainsString('width="350"', $html);
    }

    public function testContentWidthWithPadding(): void
    {
        $image = new Image(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'padding' => '20px 40px',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $image->render();

        // Container width (600) minus left+right padding (80) = 520
        $this->assertStringContainsString('width="520"', $html);
    }

    public function testRenderWithSrcset(): void
    {
        $image = new Image(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'srcset' => 'image-320w.jpg 320w, image-640w.jpg 640w',
                'sizes' => '(max-width: 320px) 280px, 640px',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $image->render();

        $this->assertStringContainsString('srcset="image-320w.jpg 320w, image-640w.jpg 640w"', $html);
        $this->assertStringContainsString('sizes="(max-width: 320px) 280px, 640px"', $html);
    }

    private function createContext(int $containerWidth = 600): RenderContext
    {
        return new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: $containerWidth,
        );
    }
}
