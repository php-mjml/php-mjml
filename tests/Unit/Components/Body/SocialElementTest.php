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
use PhpMjml\Components\Body\SocialElement;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class SocialElementTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-social-element', SocialElement::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertTrue(SocialElement::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = SocialElement::getDefaultAttributes();

        $this->assertSame('', $defaults['alt']);
        $this->assertSame('left', $defaults['align']);
        $this->assertSame('left', $defaults['icon-position']);
        $this->assertSame('#000', $defaults['color']);
        $this->assertSame('3px', $defaults['border-radius']);
        $this->assertSame('Ubuntu, Helvetica, Arial, sans-serif', $defaults['font-family']);
        $this->assertSame('13px', $defaults['font-size']);
        $this->assertSame('1', $defaults['line-height']);
        $this->assertSame('4px', $defaults['padding']);
        $this->assertSame('4px 4px 4px 0', $defaults['text-padding']);
        $this->assertSame('_blank', $defaults['target']);
        $this->assertSame('none', $defaults['text-decoration']);
        $this->assertSame('middle', $defaults['vertical-align']);
    }

    public function testRenderBasicFacebookIcon(): void
    {
        $element = new SocialElement(
            attributes: ['name' => 'facebook'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $element->render();

        $this->assertStringContainsString('<tr', $html);
        $this->assertStringContainsString('<td', $html);
        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString('facebook.png', $html);
        $this->assertStringContainsString('background:#3b5998', $html);
    }

    public function testRenderWithLink(): void
    {
        $element = new SocialElement(
            attributes: [
                'name' => 'facebook',
                'href' => 'https://facebook.com/example',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $element->render();

        $this->assertStringContainsString('<a', $html);
        $this->assertStringContainsString('href="https://www.facebook.com/sharer/sharer.php?u=https://facebook.com/example"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testRenderWithTextContent(): void
    {
        $element = new SocialElement(
            attributes: [
                'name' => 'facebook',
                'href' => 'https://facebook.com',
            ],
            children: [],
            content: 'Share on Facebook',
            context: $this->createContext(),
        );

        $html = $element->render();

        $this->assertStringContainsString('Share on Facebook', $html);
    }

    public function testRenderWithCustomSrc(): void
    {
        $element = new SocialElement(
            attributes: [
                'src' => 'https://example.com/custom-icon.png',
                'href' => 'https://example.com',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $element->render();

        $this->assertStringContainsString('src="https://example.com/custom-icon.png"', $html);
    }

    public function testRenderIconPositionRight(): void
    {
        $element = new SocialElement(
            attributes: [
                'name' => 'twitter',
                'icon-position' => 'right',
                'href' => 'https://twitter.com',
            ],
            children: [],
            content: 'Follow us',
            context: $this->createContext(),
        );

        $html = $element->render();

        // When icon-position is right, content should come before icon
        $contentPos = strpos($html, 'Follow us');
        $imgPos = strpos($html, '<img');

        $this->assertNotFalse($contentPos);
        $this->assertNotFalse($imgPos);
        $this->assertLessThan($imgPos, $contentPos, 'Content should appear before icon when icon-position is right');
    }

    public function testRenderWithNoshareVariant(): void
    {
        $element = new SocialElement(
            attributes: [
                'name' => 'facebook-noshare',
                'href' => 'https://facebook.com/mypage',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $element->render();

        // noshare variant should use the href directly without transformation
        $this->assertStringContainsString('href="https://facebook.com/mypage"', $html);
        $this->assertStringContainsString('facebook.png', $html);
    }

    public function testRenderWithXNetwork(): void
    {
        $element = new SocialElement(
            attributes: ['name' => 'x'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $element->render();

        $this->assertStringContainsString('twitter-x.png', $html);
        $this->assertStringContainsString('background:#000000', $html);
    }

    public function testGetStyles(): void
    {
        $element = new SocialElement(
            attributes: [
                'name' => 'facebook',
                'padding' => '10px',
                'border-radius' => '5px',
                'color' => '#ffffff',
                'font-size' => '14px',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $styles = $element->getStyles();

        $this->assertArrayHasKey('td', $styles);
        $this->assertArrayHasKey('table', $styles);
        $this->assertArrayHasKey('icon', $styles);
        $this->assertArrayHasKey('img', $styles);
        $this->assertArrayHasKey('tdText', $styles);
        $this->assertArrayHasKey('text', $styles);

        $this->assertSame('10px', $styles['td']['padding']);
        $this->assertSame('5px', $styles['img']['border-radius']);
        $this->assertSame('#ffffff', $styles['text']['color']);
        $this->assertSame('14px', $styles['text']['font-size']);
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
