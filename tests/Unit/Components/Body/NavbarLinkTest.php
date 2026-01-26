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
use PhpMjml\Components\Body\NavbarLink;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class NavbarLinkTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-navbar-link', NavbarLink::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertTrue(NavbarLink::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = NavbarLink::getDefaultAttributes();

        $this->assertSame('#000000', $defaults['color']);
        $this->assertSame('Ubuntu, Helvetica, Arial, sans-serif', $defaults['font-family']);
        $this->assertSame('13px', $defaults['font-size']);
        $this->assertSame('normal', $defaults['font-weight']);
        $this->assertSame('22px', $defaults['line-height']);
        $this->assertSame('15px 10px', $defaults['padding']);
        $this->assertSame('_blank', $defaults['target']);
        $this->assertSame('none', $defaults['text-decoration']);
        $this->assertSame('uppercase', $defaults['text-transform']);
    }

    public function testRenderBasic(): void
    {
        $context = $this->createContext();

        $link = new NavbarLink(
            attributes: ['href' => '/about'],
            children: [],
            content: 'About Us',
            context: $context,
        );

        $html = $link->render();

        $this->assertStringContainsString('<!--[if mso | IE]>', $html);
        $this->assertStringContainsString('<td', $html);
        $this->assertStringContainsString('</td>', $html);
        $this->assertStringContainsString('<a', $html);
        $this->assertStringContainsString('href="/about"', $html);
        $this->assertStringContainsString('class="mj-link"', $html);
        $this->assertStringContainsString('About Us', $html);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testRenderWithBaseUrl(): void
    {
        $context = $this->createContextWithBaseUrl('https://example.com');

        $link = new NavbarLink(
            attributes: ['href' => '/contact'],
            children: [],
            content: 'Contact',
            context: $context,
        );

        $html = $link->render();

        $this->assertStringContainsString('href="https://example.com/contact"', $html);
    }

    public function testRenderWithoutBaseUrl(): void
    {
        $context = $this->createContext();

        $link = new NavbarLink(
            attributes: ['href' => 'https://external.com/page'],
            children: [],
            content: 'External',
            context: $context,
        );

        $html = $link->render();

        $this->assertStringContainsString('href="https://external.com/page"', $html);
    }

    public function testRenderWithCssClass(): void
    {
        $context = $this->createContext();

        $link = new NavbarLink(
            attributes: [
                'href' => '/home',
                'css-class' => 'custom-link',
            ],
            children: [],
            content: 'Home',
            context: $context,
        );

        $html = $link->render();

        $this->assertStringContainsString('class="mj-link custom-link"', $html);
        // MSO td should have suffixed class
        $this->assertStringContainsString('custom-link-outlook', $html);
    }

    public function testRenderWithRel(): void
    {
        $context = $this->createContext();

        $link = new NavbarLink(
            attributes: [
                'href' => '/external',
                'rel' => 'noopener noreferrer',
            ],
            children: [],
            content: 'External Link',
            context: $context,
        );

        $html = $link->render();

        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
    }

    public function testRenderWithName(): void
    {
        $context = $this->createContext();

        $link = new NavbarLink(
            attributes: [
                'href' => '/section',
                'name' => 'section-link',
            ],
            children: [],
            content: 'Section',
            context: $context,
        );

        $html = $link->render();

        $this->assertStringContainsString('name="section-link"', $html);
    }

    public function testRenderWithCustomTarget(): void
    {
        $context = $this->createContext();

        $link = new NavbarLink(
            attributes: [
                'href' => '/same-page',
                'target' => '_self',
            ],
            children: [],
            content: 'Same Page',
            context: $context,
        );

        $html = $link->render();

        $this->assertStringContainsString('target="_self"', $html);
    }

    public function testGetStyles(): void
    {
        $context = $this->createContext();

        $link = new NavbarLink(
            attributes: [
                'color' => '#ff0000',
                'padding' => '20px',
            ],
            children: [],
            content: 'Test',
            context: $context,
        );

        $styles = $link->getStyles();

        $this->assertArrayHasKey('a', $styles);
        $this->assertArrayHasKey('td', $styles);
        $this->assertSame('#ff0000', $styles['a']['color']);
        $this->assertSame('20px', $styles['a']['padding']);
        $this->assertSame('20px', $styles['td']['padding']);
    }

    private function createContext(): RenderContext
    {
        return new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
        );
    }

    private function createContextWithBaseUrl(string $baseUrl): RenderContext
    {
        return new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
            navbarBaseUrl: $baseUrl,
        );
    }
}
