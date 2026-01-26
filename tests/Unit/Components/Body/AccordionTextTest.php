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
use PhpMjml\Components\Body\AccordionText;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class AccordionTextTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-accordion-text', AccordionText::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertTrue(AccordionText::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = AccordionText::getDefaultAttributes();

        $this->assertSame('13px', $defaults['font-size']);
        $this->assertSame('1', $defaults['line-height']);
        $this->assertSame('16px', $defaults['padding']);
    }

    public function testRenderBasic(): void
    {
        $context = $this->createContext();

        $text = new AccordionText(
            attributes: [],
            children: [],
            content: 'Test content goes here',
            context: $context,
        );

        $html = $text->render();

        $this->assertStringContainsString('<div', $html);
        $this->assertStringContainsString('class="mj-accordion-content"', $html);
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('Test content goes here', $html);
    }

    public function testRenderWithStyling(): void
    {
        $context = $this->createContext();

        $text = new AccordionText(
            attributes: [
                'background-color' => '#f5f5f5',
                'color' => '#666666',
                'font-size' => '14px',
                'line-height' => '1.5',
            ],
            children: [],
            content: 'Styled content',
            context: $context,
        );

        $html = $text->render();

        $this->assertStringContainsString('background:#f5f5f5', $html);
        $this->assertStringContainsString('color:#666666', $html);
        $this->assertStringContainsString('font-size:14px', $html);
        $this->assertStringContainsString('line-height:1.5', $html);
    }

    public function testRenderWithCssClass(): void
    {
        $context = $this->createContext();

        $text = new AccordionText(
            attributes: ['css-class' => 'custom-text'],
            children: [],
            content: 'Content with class',
            context: $context,
        );

        $html = $text->render();

        $this->assertStringContainsString('class="custom-text"', $html);
    }

    public function testGetStyles(): void
    {
        $context = $this->createContext();

        $text = new AccordionText(
            attributes: [
                'background-color' => '#ffffff',
                'color' => '#333333',
                'font-size' => '16px',
            ],
            children: [],
            content: 'Test',
            context: $context,
        );

        $styles = $text->getStyles();

        $this->assertArrayHasKey('td', $styles);
        $this->assertArrayHasKey('table', $styles);

        $this->assertSame('#ffffff', $styles['td']['background']);
        $this->assertSame('#333333', $styles['td']['color']);
        $this->assertSame('16px', $styles['td']['font-size']);
        $this->assertSame('100%', $styles['table']['width']);
    }

    public function testFontFamilyInheritance(): void
    {
        // Create context with accordion font family
        $context = new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
            accordionSettings: ['fontFamily' => 'Arial, sans-serif'],
        );

        $text = new AccordionText(
            attributes: [],
            children: [],
            content: 'Test',
            context: $context,
        );

        $styles = $text->getStyles();

        $this->assertSame('Arial, sans-serif', $styles['td']['font-family']);
    }

    public function testElementFontFamilyOverridesAccordion(): void
    {
        // Create context with both accordion and element font families
        $context = new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
            accordionSettings: [
                'fontFamily' => 'Arial, sans-serif',
                'elementFontFamily' => 'Georgia, serif',
            ],
        );

        $text = new AccordionText(
            attributes: [],
            children: [],
            content: 'Test',
            context: $context,
        );

        $styles = $text->getStyles();

        // Element font family should take precedence
        $this->assertSame('Georgia, serif', $styles['td']['font-family']);
    }

    public function testExplicitFontFamilyOverridesInheritance(): void
    {
        // Create context with font families
        $context = new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
            accordionSettings: [
                'fontFamily' => 'Arial, sans-serif',
                'elementFontFamily' => 'Georgia, serif',
            ],
        );

        $text = new AccordionText(
            attributes: ['font-family' => 'Helvetica, sans-serif'],
            children: [],
            content: 'Test',
            context: $context,
        );

        $styles = $text->getStyles();

        // Explicit attribute should take precedence
        $this->assertSame('Helvetica, sans-serif', $styles['td']['font-family']);
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
