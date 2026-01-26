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
use PhpMjml\Components\Body\Carousel;
use PhpMjml\Components\Body\CarouselImage;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class CarouselTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-carousel', Carousel::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertFalse(Carousel::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Carousel::getDefaultAttributes();

        $this->assertSame('center', $defaults['align']);
        $this->assertSame('6px', $defaults['border-radius']);
        $this->assertSame('44px', $defaults['icon-width']);
        $this->assertSame('https://i.imgur.com/xTh3hln.png', $defaults['left-icon']);
        $this->assertSame('https://i.imgur.com/os7o9kz.png', $defaults['right-icon']);
        $this->assertSame('visible', $defaults['thumbnails']);
        $this->assertSame('2px solid transparent', $defaults['tb-border']);
        $this->assertSame('6px', $defaults['tb-border-radius']);
        $this->assertSame('#fead0d', $defaults['tb-hover-border-color']);
        $this->assertSame('#ccc', $defaults['tb-selected-border-color']);
    }

    public function testRenderEmptyCarouselReturnsEmpty(): void
    {
        $context = $this->createContext();

        $carousel = new Carousel(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $html = $carousel->render();

        $this->assertSame('', $html);
    }

    public function testRenderWithImages(): void
    {
        $context = $this->createContext();

        $images = [
            new CarouselImage(
                attributes: ['src' => 'https://example.com/1.jpg', 'alt' => 'Image 1'],
                children: [],
                content: '',
                context: $context,
            ),
            new CarouselImage(
                attributes: ['src' => 'https://example.com/2.jpg', 'alt' => 'Image 2'],
                children: [],
                content: '',
                context: $context,
            ),
        ];

        $carousel = new Carousel(
            attributes: [],
            children: $images,
            content: '',
            context: $context,
        );

        $html = $carousel->render();

        // Check for MSO conditional wrapper
        $this->assertStringContainsString('<!--[if !mso]><!-->', $html);
        $this->assertStringContainsString('<!--<![endif]-->', $html);

        // Check for carousel structure
        $this->assertStringContainsString('class="mj-carousel"', $html);
        $this->assertStringContainsString('mj-carousel-content', $html);
        $this->assertStringContainsString('mj-carousel-main', $html);

        // Check for radio inputs
        $this->assertStringContainsString('type="radio"', $html);
        $this->assertStringContainsString('mj-carousel-radio', $html);

        // Check for images
        $this->assertStringContainsString('https://example.com/1.jpg', $html);
        $this->assertStringContainsString('https://example.com/2.jpg', $html);
        $this->assertStringContainsString('mj-carousel-image-1', $html);
        $this->assertStringContainsString('mj-carousel-image-2', $html);

        // Check for navigation controls
        $this->assertStringContainsString('mj-carousel-previous', $html);
        $this->assertStringContainsString('mj-carousel-next', $html);

        // Check for fallback (MSO conditional)
        $this->assertStringContainsString('<!--[if mso]>', $html);
    }

    public function testRenderWithThumbnails(): void
    {
        $context = $this->createContext();

        $images = [
            new CarouselImage(
                attributes: ['src' => 'https://example.com/1.jpg'],
                children: [],
                content: '',
                context: $context,
            ),
        ];

        $carousel = new Carousel(
            attributes: ['thumbnails' => 'visible'],
            children: $images,
            content: '',
            context: $context,
        );

        $html = $carousel->render();

        $this->assertStringContainsString('mj-carousel-thumbnail', $html);
    }

    public function testRenderWithHiddenThumbnails(): void
    {
        $context = $this->createContext();

        $images = [
            new CarouselImage(
                attributes: ['src' => 'https://example.com/1.jpg'],
                children: [],
                content: '',
                context: $context,
            ),
        ];

        $carousel = new Carousel(
            attributes: ['thumbnails' => 'hidden'],
            children: $images,
            content: '',
            context: $context,
        );

        $html = $carousel->render();

        // When thumbnails are hidden, there should be no thumbnail links
        $this->assertStringNotContainsString('mj-carousel-thumbnail', $html);
    }

    public function testGetChildContext(): void
    {
        $context = $this->createContext();

        $carousel = new Carousel(
            attributes: [
                'thumbnails' => 'visible',
                'tb-border' => '3px solid red',
                'tb-border-radius' => '10px',
                'border-radius' => '8px',
            ],
            children: [],
            content: '',
            context: $context,
        );

        $childContext = $carousel->getChildContext();

        $this->assertArrayHasKey('carouselId', $childContext);
        $this->assertSame('visible', $childContext['thumbnails']);
        $this->assertSame('3px solid red', $childContext['tb-border']);
        $this->assertSame('10px', $childContext['tb-border-radius']);
        $this->assertSame('8px', $childContext['border-radius']);
        $this->assertArrayHasKey('tb-width', $childContext);
    }

    public function testComponentHeadStyleIsAdded(): void
    {
        $context = $this->createContext();

        $images = [
            new CarouselImage(
                attributes: ['src' => 'https://example.com/1.jpg'],
                children: [],
                content: '',
                context: $context,
            ),
            new CarouselImage(
                attributes: ['src' => 'https://example.com/2.jpg'],
                children: [],
                content: '',
                context: $context,
            ),
        ];

        $carousel = new Carousel(
            attributes: [],
            children: $images,
            content: '',
            context: $context,
        );

        $carousel->render();

        $headStyles = $context->getComponentHeadStyles();
        $this->assertNotEmpty($headStyles);

        $style = $headStyles[0];
        $this->assertStringContainsString('.mj-carousel', $style);
        $this->assertStringContainsString('user-select', $style);
        $this->assertStringContainsString('.mj-carousel-radio', $style);
        $this->assertStringContainsString('.mj-carousel-thumbnail', $style);
    }

    public function testGetStyles(): void
    {
        $context = $this->createContext();

        $carousel = new Carousel(
            attributes: ['icon-width' => '50px'],
            children: [],
            content: '',
            context: $context,
        );

        // Styles are inlined directly, so getStyles returns empty
        $styles = $carousel->getStyles();
        $this->assertSame([], $styles);
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
