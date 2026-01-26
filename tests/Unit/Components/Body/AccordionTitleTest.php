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
use PhpMjml\Components\Body\AccordionTitle;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class AccordionTitleTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-accordion-title', AccordionTitle::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertTrue(AccordionTitle::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = AccordionTitle::getDefaultAttributes();

        $this->assertSame('13px', $defaults['font-size']);
        $this->assertSame('16px', $defaults['padding']);
    }

    public function testRenderBasic(): void
    {
        $context = $this->createContext();

        $title = new AccordionTitle(
            attributes: [],
            children: [],
            content: 'Test Title',
            context: $context,
        );

        $html = $title->render();

        $this->assertStringContainsString('<div', $html);
        $this->assertStringContainsString('class="mj-accordion-title"', $html);
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('Test Title', $html);
        $this->assertStringContainsString('mj-accordion-ico', $html);
        $this->assertStringContainsString('mj-accordion-more', $html);
        $this->assertStringContainsString('mj-accordion-less', $html);
    }

    public function testRenderWithStyling(): void
    {
        $context = $this->createContext();

        $title = new AccordionTitle(
            attributes: [
                'background-color' => '#e0e0e0',
                'color' => '#333333',
                'font-size' => '16px',
            ],
            children: [],
            content: 'Styled Title',
            context: $context,
        );

        $html = $title->render();

        $this->assertStringContainsString('background-color:#e0e0e0', $html);
        $this->assertStringContainsString('color:#333333', $html);
        $this->assertStringContainsString('font-size:16px', $html);
    }

    public function testRenderWithIconPositionLeft(): void
    {
        $context = $this->createContextWithIconPosition('left');

        $title = new AccordionTitle(
            attributes: [],
            children: [],
            content: 'Title',
            context: $context,
        );

        $html = $title->render();

        // Icons should appear before title when position is left
        $iconPos = strpos($html, 'mj-accordion-ico');
        $titlePos = strpos($html, 'Title');

        $this->assertNotFalse($iconPos);
        $this->assertNotFalse($titlePos);
        $this->assertLessThan($titlePos, $iconPos);
    }

    public function testRenderWithIconPositionRight(): void
    {
        $context = $this->createContextWithIconPosition('right');

        $title = new AccordionTitle(
            attributes: [],
            children: [],
            content: 'Title',
            context: $context,
        );

        $html = $title->render();

        // Title should appear before icons when position is right
        $iconPos = strpos($html, 'mj-accordion-ico');
        $titlePos = strpos($html, '> Title <');

        $this->assertNotFalse($iconPos);
        $this->assertNotFalse($titlePos);
        $this->assertLessThan($iconPos, $titlePos);
    }

    public function testGetStyles(): void
    {
        $context = $this->createContext();

        $title = new AccordionTitle(
            attributes: [
                'background-color' => '#ffffff',
                'color' => '#000000',
            ],
            children: [],
            content: 'Test',
            context: $context,
        );

        $styles = $title->getStyles();

        $this->assertArrayHasKey('td', $styles);
        $this->assertArrayHasKey('table', $styles);
        $this->assertArrayHasKey('td2', $styles);
        $this->assertArrayHasKey('img', $styles);

        $this->assertSame('100%', $styles['td']['width']);
        $this->assertSame('#ffffff', $styles['td']['background-color']);
        $this->assertSame('#000000', $styles['td']['color']);
        $this->assertSame('100%', $styles['table']['width']);
        $this->assertSame('none', $styles['img']['display']);
    }

    public function testFontFamilyInheritance(): void
    {
        // Create context with accordion font family
        $context = new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
            accordionFontFamily: 'Arial, sans-serif',
        );

        $title = new AccordionTitle(
            attributes: [],
            children: [],
            content: 'Test',
            context: $context,
        );

        $styles = $title->getStyles();

        $this->assertSame('Arial, sans-serif', $styles['td']['font-family']);
    }

    public function testElementFontFamilyOverridesAccordion(): void
    {
        // Create context with both accordion and element font families
        $context = new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
            accordionFontFamily: 'Arial, sans-serif',
            elementFontFamily: 'Georgia, serif',
        );

        $title = new AccordionTitle(
            attributes: [],
            children: [],
            content: 'Test',
            context: $context,
        );

        $styles = $title->getStyles();

        // Element font family should take precedence
        $this->assertSame('Georgia, serif', $styles['td']['font-family']);
    }

    private function createContext(): RenderContext
    {
        return new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
        );
    }

    private function createContextWithIconPosition(string $position): RenderContext
    {
        return new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
            accordionIconPosition: $position,
        );
    }
}
