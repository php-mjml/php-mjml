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
use PhpMjml\Components\Body\Navbar;
use PhpMjml\Components\Body\NavbarLink;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class NavbarTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-navbar', Navbar::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertFalse(Navbar::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Navbar::getDefaultAttributes();

        $this->assertSame('center', $defaults['align']);
        $this->assertNull($defaults['base-url']);
        $this->assertNull($defaults['hamburger']);
        $this->assertSame('center', $defaults['ico-align']);
        $this->assertSame('&#9776;', $defaults['ico-open']);
        $this->assertSame('&#8855;', $defaults['ico-close']);
        $this->assertSame('#000000', $defaults['ico-color']);
        $this->assertSame('30px', $defaults['ico-font-size']);
        $this->assertSame('Ubuntu, Helvetica, Arial, sans-serif', $defaults['ico-font-family']);
        $this->assertSame('uppercase', $defaults['ico-text-transform']);
        $this->assertSame('10px', $defaults['ico-padding']);
        $this->assertSame('none', $defaults['ico-text-decoration']);
        $this->assertSame('30px', $defaults['ico-line-height']);
    }

    public function testRenderBasic(): void
    {
        $context = $this->createContext();

        $link = new NavbarLink(
            attributes: ['href' => '/home'],
            children: [],
            content: 'Home',
            context: $context,
        );

        $navbar = new Navbar(
            attributes: [],
            children: [$link],
            content: '',
            context: $context,
        );

        $html = $navbar->render();

        $this->assertStringContainsString('class="mj-inline-links"', $html);
        $this->assertStringContainsString('<!--[if mso | IE]>', $html);
        $this->assertStringContainsString('role="presentation"', $html);
        $this->assertStringContainsString('align="center"', $html);
    }

    public function testRenderWithHamburger(): void
    {
        $context = $this->createContext();

        $link = new NavbarLink(
            attributes: ['href' => '/home'],
            children: [],
            content: 'Home',
            context: $context,
        );

        $navbar = new Navbar(
            attributes: ['hamburger' => 'hamburger'],
            children: [$link],
            content: '',
            context: $context,
        );

        $html = $navbar->render();

        // Check hamburger elements
        $this->assertStringContainsString('mj-menu-checkbox', $html);
        $this->assertStringContainsString('mj-menu-trigger', $html);
        $this->assertStringContainsString('mj-menu-label', $html);
        $this->assertStringContainsString('mj-menu-icon-open', $html);
        $this->assertStringContainsString('mj-menu-icon-close', $html);
        $this->assertStringContainsString('&#9776;', $html); // Default open icon
        $this->assertStringContainsString('&#8855;', $html); // Default close icon
    }

    public function testRenderWithCustomIcons(): void
    {
        $context = $this->createContext();

        $navbar = new Navbar(
            attributes: [
                'hamburger' => 'hamburger',
                'ico-open' => 'MENU',
                'ico-close' => 'CLOSE',
            ],
            children: [],
            content: '',
            context: $context,
        );

        $html = $navbar->render();

        $this->assertStringContainsString('MENU', $html);
        $this->assertStringContainsString('CLOSE', $html);
    }

    public function testGetChildContext(): void
    {
        $context = $this->createContext();

        $navbar = new Navbar(
            attributes: ['base-url' => 'https://example.com'],
            children: [],
            content: '',
            context: $context,
        );

        $childContext = $navbar->getChildContext();

        $this->assertArrayHasKey('componentData', $childContext);
        $this->assertArrayHasKey('navbar', $childContext['componentData']);
        $this->assertSame('https://example.com', $childContext['componentData']['navbar']['baseUrl']);
    }

    public function testGetStyles(): void
    {
        $context = $this->createContext();

        $navbar = new Navbar(
            attributes: ['ico-color' => '#ff0000'],
            children: [],
            content: '',
            context: $context,
        );

        $styles = $navbar->getStyles();

        $this->assertArrayHasKey('div', $styles);
        $this->assertArrayHasKey('label', $styles);
        $this->assertArrayHasKey('trigger', $styles);
        $this->assertArrayHasKey('icoOpen', $styles);
        $this->assertArrayHasKey('icoClose', $styles);
        $this->assertSame('#ff0000', $styles['label']['color']);
    }

    public function testHamburgerAddsHeadStyle(): void
    {
        $context = $this->createContext();

        $navbar = new Navbar(
            attributes: ['hamburger' => 'hamburger'],
            children: [],
            content: '',
            context: $context,
        );

        $navbar->render();

        $headStyles = $context->globalData->headStyle;
        $this->assertArrayHasKey('mj-navbar', $headStyles);

        $style = $headStyles['mj-navbar'];
        $this->assertStringContainsString('.mj-menu-checkbox', $style);
        $this->assertStringContainsString('.mj-inline-links', $style);
        $this->assertStringContainsString('.mj-menu-trigger', $style);
        $this->assertStringContainsString('@media only screen', $style);
        $this->assertStringContainsString('479px', $style); // breakpoint - 1
    }

    public function testRenderWithLeftAlign(): void
    {
        $context = $this->createContext();

        $navbar = new Navbar(
            attributes: ['align' => 'left'],
            children: [],
            content: '',
            context: $context,
        );

        $html = $navbar->render();

        $this->assertStringContainsString('align="left"', $html);
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
