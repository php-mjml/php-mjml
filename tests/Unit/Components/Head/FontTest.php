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
use PhpMjml\Components\Head\Font;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class FontTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-font', Font::getComponentName());
    }

    public function testHandleAddsFontToContext(): void
    {
        $context = $this->createContext();
        $fontName = 'Open Sans';
        $fontHref = 'https://fonts.googleapis.com/css?family=Open+Sans';

        $font = new Font(
            attributes: ['name' => $fontName, 'href' => $fontHref],
            children: [],
            content: '',
            context: $context,
        );

        $font->handle($context);

        $this->assertArrayHasKey($fontName, $context->fonts);
        $this->assertSame($fontHref, $context->fonts[$fontName]);
    }

    public function testHandleWithMissingNameDoesNotAddFont(): void
    {
        $context = $this->createContext();

        $font = new Font(
            attributes: ['href' => 'https://fonts.googleapis.com/css?family=Roboto'],
            children: [],
            content: '',
            context: $context,
        );

        $font->handle($context);

        $this->assertSame([], $context->fonts);
    }

    public function testHandleWithMissingHrefDoesNotAddFont(): void
    {
        $context = $this->createContext();

        $font = new Font(
            attributes: ['name' => 'Roboto'],
            children: [],
            content: '',
            context: $context,
        );

        $font->handle($context);

        $this->assertSame([], $context->fonts);
    }

    public function testRenderReturnsEmptyString(): void
    {
        $font = new Font(
            attributes: ['name' => 'Arial', 'href' => 'https://example.com'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $this->assertSame('', $font->render());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Font::getDefaultAttributes();

        $this->assertSame([], $defaults);
    }

    public function testAllowedAttributes(): void
    {
        $allowed = Font::getAllowedAttributes();

        $this->assertSame(['name' => 'string', 'href' => 'string'], $allowed);
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
