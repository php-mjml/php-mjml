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
use PhpMjml\Components\Body\CarouselImage;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class CarouselImageTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-carousel-image', CarouselImage::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertTrue(CarouselImage::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = CarouselImage::getDefaultAttributes();

        $this->assertSame('', $defaults['alt']);
        $this->assertSame('_blank', $defaults['target']);
    }

    public function testRenderReturnsEmpty(): void
    {
        $context = $this->createContext();

        $image = new CarouselImage(
            attributes: ['src' => 'https://example.com/image.jpg'],
            children: [],
            content: '',
            context: $context,
        );

        // The render method returns empty because rendering is done
        // via renderImage, renderRadio, renderThumbnail
        $this->assertSame('', $image->render());
    }

    public function testRenderRadio(): void
    {
        $context = $this->createContext();

        $image = new CarouselImage(
            attributes: ['src' => 'https://example.com/image.jpg'],
            children: [],
            content: '',
            context: $context,
        );

        $radioHtml = $image->renderRadio('abc123', 0);

        $this->assertStringContainsString('type="radio"', $radioHtml);
        $this->assertStringContainsString('mj-carousel-abc123-radio', $radioHtml);
        $this->assertStringContainsString('mj-carousel-abc123-radio-1', $radioHtml);
        $this->assertStringContainsString('checked="checked"', $radioHtml);
        $this->assertStringContainsString('name="mj-carousel-radio-abc123"', $radioHtml);
        $this->assertStringContainsString('id="mj-carousel-abc123-radio-1"', $radioHtml);
    }

    public function testRenderRadioNotCheckedForNonFirstIndex(): void
    {
        $context = $this->createContext();

        $image = new CarouselImage(
            attributes: ['src' => 'https://example.com/image.jpg'],
            children: [],
            content: '',
            context: $context,
        );

        $radioHtml = $image->renderRadio('abc123', 1);

        $this->assertStringContainsString('mj-carousel-abc123-radio-2', $radioHtml);
        $this->assertStringNotContainsString('checked', $radioHtml);
    }

    public function testRenderThumbnail(): void
    {
        $context = $this->createContext();

        $image = new CarouselImage(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'alt' => 'Test Image',
            ],
            children: [],
            content: '',
            context: $context,
        );

        $thumbnailHtml = $image->renderThumbnail(
            'abc123',
            0,
            '2px solid transparent',
            '6px',
            '100px',
            'visible',
        );

        $this->assertStringContainsString('mj-carousel-thumbnail', $thumbnailHtml);
        $this->assertStringContainsString('mj-carousel-abc123-thumbnail', $thumbnailHtml);
        $this->assertStringContainsString('mj-carousel-abc123-thumbnail-1', $thumbnailHtml);
        $this->assertStringContainsString('href="#1"', $thumbnailHtml);
        $this->assertStringContainsString('for="mj-carousel-abc123-radio-1"', $thumbnailHtml);
        $this->assertStringContainsString('https://example.com/image.jpg', $thumbnailHtml);
        $this->assertStringContainsString('alt="Test Image"', $thumbnailHtml);
    }

    public function testRenderThumbnailWithCustomSrc(): void
    {
        $context = $this->createContext();

        $image = new CarouselImage(
            attributes: [
                'src' => 'https://example.com/full.jpg',
                'thumbnails-src' => 'https://example.com/thumb.jpg',
            ],
            children: [],
            content: '',
            context: $context,
        );

        $thumbnailHtml = $image->renderThumbnail(
            'abc123',
            0,
            '2px solid transparent',
            '6px',
            '100px',
            'visible',
        );

        $this->assertStringContainsString('https://example.com/thumb.jpg', $thumbnailHtml);
        $this->assertStringNotContainsString('https://example.com/full.jpg', $thumbnailHtml);
    }

    public function testRenderThumbnailWithSupportedMode(): void
    {
        $context = $this->createContext();

        $image = new CarouselImage(
            attributes: ['src' => 'https://example.com/image.jpg'],
            children: [],
            content: '',
            context: $context,
        );

        // With 'supported' mode, the display is none
        $thumbnailHtml = $image->renderThumbnail(
            'abc123',
            0,
            '2px solid transparent',
            '6px',
            '100px',
            'supported',
        );

        // When thumbnail mode is 'supported', display should be 'none'
        $this->assertStringContainsString('border:2px solid transparent', $thumbnailHtml);
        $this->assertStringContainsString('border-radius:6px', $thumbnailHtml);
        $this->assertStringContainsString('display:none', $thumbnailHtml);
    }

    public function testRenderImage(): void
    {
        $context = $this->createContext();

        $image = new CarouselImage(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'alt' => 'Test Image',
                'title' => 'Image Title',
            ],
            children: [],
            content: '',
            context: $context,
        );

        $imageHtml = $image->renderImage(0, '6px');

        $this->assertStringContainsString('mj-carousel-image', $imageHtml);
        $this->assertStringContainsString('mj-carousel-image-1', $imageHtml);
        $this->assertStringContainsString('src="https://example.com/image.jpg"', $imageHtml);
        $this->assertStringContainsString('alt="Test Image"', $imageHtml);
        $this->assertStringContainsString('title="Image Title"', $imageHtml);
        // border-radius is passed to renderImage and applied to img style
        $this->assertStringContainsString('<img', $imageHtml);
        $this->assertStringContainsString('width="600"', $imageHtml);
    }

    public function testRenderImageWithHref(): void
    {
        $context = $this->createContext();

        $image = new CarouselImage(
            attributes: [
                'src' => 'https://example.com/image.jpg',
                'href' => 'https://example.com/link',
                'rel' => 'noopener',
            ],
            children: [],
            content: '',
            context: $context,
        );

        $imageHtml = $image->renderImage(0, '6px');

        $this->assertStringContainsString('<a ', $imageHtml);
        $this->assertStringContainsString('href="https://example.com/link"', $imageHtml);
        $this->assertStringContainsString('rel="noopener"', $imageHtml);
        $this->assertStringContainsString('target="_blank"', $imageHtml);
    }

    public function testRenderImageNotFirst(): void
    {
        $context = $this->createContext();

        $image = new CarouselImage(
            attributes: ['src' => 'https://example.com/image.jpg'],
            children: [],
            content: '',
            context: $context,
        );

        $imageHtml = $image->renderImage(1, '6px');

        $this->assertStringContainsString('mj-carousel-image-2', $imageHtml);
        // Non-first images should be hidden initially
        // The actual CSS for hiding is applied by the stylesheet, not inline here
        // Just verify the correct class is applied
        $this->assertStringContainsString('mj-carousel-image-2', $imageHtml);
    }

    public function testRenderImageFirst(): void
    {
        $context = $this->createContext();

        $image = new CarouselImage(
            attributes: ['src' => 'https://example.com/image.jpg'],
            children: [],
            content: '',
            context: $context,
        );

        $imageHtml = $image->renderImage(0, '6px');

        $this->assertStringContainsString('mj-carousel-image-1', $imageHtml);
        // First image should not have display:none
        $this->assertStringContainsString('style=""', $imageHtml);
    }

    public function testGetStyles(): void
    {
        $context = $this->createContext();

        $image = new CarouselImage(
            attributes: ['border-radius' => '10px'],
            children: [],
            content: '',
            context: $context,
        );

        // Styles are inlined directly, so getStyles returns empty
        $styles = $image->getStyles();
        $this->assertSame([], $styles);
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
