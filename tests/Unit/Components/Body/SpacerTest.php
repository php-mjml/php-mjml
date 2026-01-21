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
use PhpMjml\Components\Body\Spacer;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class SpacerTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-spacer', Spacer::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertTrue(Spacer::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Spacer::getDefaultAttributes();

        $this->assertSame('20px', $defaults['height']);
    }

    public function testRenderBasicSpacer(): void
    {
        $spacer = new Spacer(
            attributes: [],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $spacer->render();

        $this->assertStringContainsString('<div', $html);
        $this->assertStringContainsString('&#8202;', $html);
        $this->assertStringContainsString('height:20px', $html);
        $this->assertStringContainsString('line-height:20px', $html);
    }

    public function testRenderWithCustomHeight(): void
    {
        $spacer = new Spacer(
            attributes: ['height' => '50px'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $spacer->render();

        $this->assertStringContainsString('height:50px', $html);
        $this->assertStringContainsString('line-height:50px', $html);
    }

    public function testGetStyles(): void
    {
        $spacer = new Spacer(
            attributes: ['height' => '100px'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $styles = $spacer->getStyles();

        $this->assertArrayHasKey('div', $styles);
        $this->assertSame('100px', $styles['div']['height']);
        $this->assertSame('100px', $styles['div']['line-height']);
    }

    public function testAllowedAttributes(): void
    {
        $allowed = Spacer::getAllowedAttributes();

        $this->assertArrayHasKey('height', $allowed);
        $this->assertArrayHasKey('border', $allowed);
        $this->assertArrayHasKey('border-bottom', $allowed);
        $this->assertArrayHasKey('border-left', $allowed);
        $this->assertArrayHasKey('border-right', $allowed);
        $this->assertArrayHasKey('border-top', $allowed);
        $this->assertArrayHasKey('container-background-color', $allowed);
        $this->assertArrayHasKey('padding', $allowed);
        $this->assertArrayHasKey('padding-bottom', $allowed);
        $this->assertArrayHasKey('padding-left', $allowed);
        $this->assertArrayHasKey('padding-right', $allowed);
        $this->assertArrayHasKey('padding-top', $allowed);
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
