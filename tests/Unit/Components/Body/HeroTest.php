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
use PhpMjml\Components\Body\Hero;
use PhpMjml\Components\Body\Text;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class HeroTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-hero', Hero::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertFalse(Hero::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Hero::getDefaultAttributes();

        $this->assertSame('fixed-height', $defaults['mode']);
        $this->assertSame('0px', $defaults['height']);
        $this->assertNull($defaults['background-url']);
        $this->assertSame('center center', $defaults['background-position']);
        $this->assertSame('0px', $defaults['padding']);
        $this->assertSame('#ffffff', $defaults['background-color']);
        $this->assertSame('top', $defaults['vertical-align']);
    }

    public function testRenderBasicHero(): void
    {
        $hero = new Hero(
            attributes: [],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $hero->render();

        $this->assertStringContainsString('<div', $html);
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('role="presentation"', $html);
        // Should have Outlook conditional comments
        $this->assertStringContainsString('<!--[if mso | IE]>', $html);
        $this->assertStringContainsString('<![endif]-->', $html);
        // Should contain mj-hero-content class
        $this->assertStringContainsString('mj-hero-content', $html);
    }

    public function testRenderWithBackgroundImage(): void
    {
        $hero = new Hero(
            attributes: [
                'background-url' => 'https://example.com/hero.jpg',
                'background-width' => '600px',
                'background-height' => '400px',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $hero->render();

        $this->assertStringContainsString('https://example.com/hero.jpg', $html);
        $this->assertStringContainsString('v:image', $html);
        $this->assertStringContainsString('background-position:center center', $html);
    }

    public function testRenderFixedHeightMode(): void
    {
        $hero = new Hero(
            attributes: [
                'mode' => 'fixed-height',
                'height' => '500px',
                'padding' => '20px',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $hero->render();

        // Height should be adjusted for padding (500 - 20 - 20 = 460)
        $this->assertStringContainsString('height="460"', $html);
        $this->assertStringContainsString('height:460px', $html);
    }

    public function testRenderFluidHeightMode(): void
    {
        $hero = new Hero(
            attributes: [
                'mode' => 'fluid-height',
                'background-url' => 'https://example.com/hero.jpg',
                'background-width' => '600px',
                'background-height' => '300px',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $hero->render();

        // Should have fluid padding-bottom based on aspect ratio (300/600 * 100 = 50%)
        $this->assertStringContainsString('padding-bottom:50%', $html);
        $this->assertStringContainsString('width:0.01%', $html);
    }

    public function testRenderWithBackgroundColor(): void
    {
        $hero = new Hero(
            attributes: ['background-color' => '#f4f4f4'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $hero->render();

        $this->assertStringContainsString('#f4f4f4', $html);
    }

    public function testRenderWithInnerBackgroundColor(): void
    {
        $hero = new Hero(
            attributes: ['inner-background-color' => '#ffffff'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $hero->render();

        $this->assertStringContainsString('background-color:#ffffff', $html);
    }

    public function testRenderWithChildren(): void
    {
        $context = $this->createContext();

        $textChild = new Text(
            attributes: [],
            children: [],
            content: 'Hero content',
            context: $context,
        );

        $hero = new Hero(
            attributes: ['height' => '400px'],
            children: [$textChild],
            content: '',
            context: $context,
        );

        $html = $hero->render();

        $this->assertStringContainsString('Hero content', $html);
    }

    public function testRenderWithVerticalAlign(): void
    {
        $hero = new Hero(
            attributes: ['vertical-align' => 'middle'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $hero->render();

        $this->assertStringContainsString('vertical-align:middle', $html);
    }

    public function testRenderWithBorderRadius(): void
    {
        $hero = new Hero(
            attributes: ['border-radius' => '10px'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $hero->render();

        $this->assertStringContainsString('border-radius:10px', $html);
    }

    public function testGetChildContext(): void
    {
        $hero = new Hero(
            attributes: ['padding' => '0 40px'],
            children: [],
            content: '',
            context: $this->createContext(600),
        );

        $childContext = $hero->getChildContext();

        // 600px - 40px left - 40px right = 520px
        $this->assertSame(520, $childContext['containerWidth']);
    }

    public function testGetStyles(): void
    {
        $hero = new Hero(
            attributes: [
                'inner-background-color' => '#ffffff',
                'background-width' => '800px',
                'background-height' => '400px',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $styles = $hero->getStyles();

        $this->assertArrayHasKey('div', $styles);
        $this->assertArrayHasKey('table', $styles);
        $this->assertArrayHasKey('td-fluid', $styles);
        $this->assertArrayHasKey('outlook-table', $styles);
        $this->assertArrayHasKey('outlook-td', $styles);
        $this->assertArrayHasKey('outlook-image', $styles);
        $this->assertArrayHasKey('outlook-inner-td', $styles);
        $this->assertArrayHasKey('inner-table', $styles);
        $this->assertArrayHasKey('inner-div', $styles);

        $this->assertSame('0 auto', $styles['div']['margin']);
        $this->assertSame('600px', $styles['div']['max-width']);
        $this->assertSame('#ffffff', $styles['inner-div']['background-color']);
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
