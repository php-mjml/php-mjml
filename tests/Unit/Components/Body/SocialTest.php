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
use PhpMjml\Components\Body\Social;
use PhpMjml\Components\Body\SocialElement;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class SocialTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-social', Social::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertFalse(Social::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Social::getDefaultAttributes();

        $this->assertSame('center', $defaults['align']);
        $this->assertSame('3px', $defaults['border-radius']);
        $this->assertSame('#333333', $defaults['color']);
        $this->assertSame('Ubuntu, Helvetica, Arial, sans-serif', $defaults['font-family']);
        $this->assertSame('13px', $defaults['font-size']);
        $this->assertSame('20px', $defaults['icon-size']);
        $this->assertNull($defaults['inner-padding']);
        $this->assertSame('22px', $defaults['line-height']);
        $this->assertSame('horizontal', $defaults['mode']);
        $this->assertSame('10px 25px', $defaults['padding']);
        $this->assertSame('none', $defaults['text-decoration']);
    }

    public function testRenderHorizontalMode(): void
    {
        $context = $this->createContext();
        $childContext = $this->createContextWithInheritedAttributes();

        $child = new SocialElement(
            attributes: ['name' => 'facebook'],
            children: [],
            content: '',
            context: $childContext,
        );

        $social = new Social(
            attributes: ['mode' => 'horizontal'],
            children: [$child],
            content: '',
            context: $context,
        );

        $html = $social->render();

        $this->assertStringContainsString('<!--[if mso | IE]><table', $html);
        $this->assertStringContainsString('display:inline-table', $html);
        $this->assertStringContainsString('<!--[if mso | IE]><td><![endif]-->', $html);
        $this->assertStringContainsString('<!--[if mso | IE]></td><![endif]-->', $html);
    }

    public function testRenderVerticalMode(): void
    {
        $context = $this->createContext();
        $childContext = $this->createContextWithInheritedAttributes();

        $child = new SocialElement(
            attributes: ['name' => 'twitter'],
            children: [],
            content: '',
            context: $childContext,
        );

        $social = new Social(
            attributes: ['mode' => 'vertical'],
            children: [$child],
            content: '',
            context: $context,
        );

        $html = $social->render();

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('<tbody>', $html);
        $this->assertStringNotContainsString('display:inline-table', $html);
        $this->assertStringContainsString('margin:0px', $html);
    }

    public function testRenderWithMultipleChildren(): void
    {
        $context = $this->createContext();
        $childContext = $this->createContextWithInheritedAttributes();

        $children = [
            new SocialElement(
                attributes: ['name' => 'facebook'],
                children: [],
                content: '',
                context: $childContext,
            ),
            new SocialElement(
                attributes: ['name' => 'twitter'],
                children: [],
                content: '',
                context: $childContext,
            ),
        ];

        $social = new Social(
            attributes: [],
            children: $children,
            content: '',
            context: $context,
        );

        $html = $social->render();

        $this->assertStringContainsString('facebook.png', $html);
        $this->assertStringContainsString('twitter.png', $html);
    }

    public function testGetChildContext(): void
    {
        $context = $this->createContext();

        $social = new Social(
            attributes: [
                'border-radius' => '5px',
                'color' => '#ffffff',
                'inner-padding' => '10px',
            ],
            children: [],
            content: '',
            context: $context,
        );

        $childContext = $social->getChildContext();

        $this->assertArrayHasKey('inheritedAttributes', $childContext);
        $inherited = $childContext['inheritedAttributes'];

        $this->assertSame('5px', $inherited['border-radius']);
        $this->assertSame('#ffffff', $inherited['color']);
        $this->assertSame('10px', $inherited['padding']); // inner-padding becomes padding
    }

    public function testGetStyles(): void
    {
        $social = new Social(
            attributes: [],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $styles = $social->getStyles();

        $this->assertArrayHasKey('tableVertical', $styles);
        $this->assertSame('0px', $styles['tableVertical']['margin']);
    }

    private function createContext(): RenderContext
    {
        return new RenderContext(
            registry: new Registry(),
            renderOptions: new RenderOptions(),
            options: ['containerWidth' => 600],
        );
    }

    private function createContextWithInheritedAttributes(): RenderContext
    {
        return new RenderContext(
            registry: new Registry(),
            renderOptions: new RenderOptions(),
            options: [
                'containerWidth' => 600,
                'inheritedAttributes' => [
                    'border-radius' => '3px',
                    'color' => '#333333',
                    'font-family' => 'Ubuntu, Helvetica, Arial, sans-serif',
                    'font-size' => '13px',
                    'icon-size' => '20px',
                    'line-height' => '22px',
                    'text-decoration' => 'none',
                ],
            ],
        );
    }
}
