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
use PhpMjml\Components\Body\Column;
use PhpMjml\Components\Body\Section;
use PhpMjml\Components\Body\Text;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class SectionTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-section', Section::getComponentName());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Section::getDefaultAttributes();

        $this->assertSame('repeat', $defaults['background-repeat']);
        $this->assertSame('auto', $defaults['background-size']);
        $this->assertSame('top center', $defaults['background-position']);
        $this->assertSame('ltr', $defaults['direction']);
        $this->assertSame('20px 0', $defaults['padding']);
        $this->assertSame('center', $defaults['text-align']);
    }

    public function testRenderBasicSection(): void
    {
        $section = new Section(
            attributes: [],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $section->render();

        $this->assertStringContainsString('<div', $html);
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('role="presentation"', $html);
        // Should have Outlook conditional comments
        $this->assertStringContainsString('<!--[if mso | IE]>', $html);
        $this->assertStringContainsString('<![endif]-->', $html);
    }

    public function testRenderWithBackgroundColor(): void
    {
        $section = new Section(
            attributes: ['background-color' => '#f4f4f4'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $section->render();

        $this->assertStringContainsString('background-color:#f4f4f4', $html);
    }

    public function testRenderWithPadding(): void
    {
        $section = new Section(
            attributes: ['padding' => '30px 20px'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $section->render();

        $this->assertStringContainsString('padding:30px 20px', $html);
    }

    public function testRenderFullWidth(): void
    {
        $section = new Section(
            attributes: ['full-width' => 'full-width'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $section->render();

        // Full-width sections have width:100% on outer table
        $this->assertStringContainsString('width:100%', $html);
    }

    public function testRenderWithBackgroundImage(): void
    {
        $section = new Section(
            attributes: [
                'background-url' => 'https://example.com/image.jpg',
                'background-size' => 'cover',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $section->render();

        // Should have VML for Outlook
        $this->assertStringContainsString('v:rect', $html);
        $this->assertStringContainsString('v:fill', $html);
        $this->assertStringContainsString('https://example.com/image.jpg', $html);
    }

    public function testRenderWithChildren(): void
    {
        $context = $this->createContext();

        $textChild = new Text(
            attributes: [],
            children: [],
            content: 'Section content',
            context: $context,
        );

        $columnChild = new Column(
            attributes: [],
            children: [$textChild],
            content: '',
            context: $context,
            props: ['nonRawSiblings' => 1],
        );

        $section = new Section(
            attributes: [],
            children: [$columnChild],
            content: '',
            context: $context,
        );

        $html = $section->render();

        $this->assertStringContainsString('Section content', $html);
    }

    public function testGetChildContext(): void
    {
        $section = new Section(
            attributes: ['padding' => '0 40px'],
            children: [],
            content: '',
            context: $this->createContext(600),
        );

        $childContext = $section->getChildContext();

        // 600px - 40px left - 40px right = 520px
        $this->assertSame(520, $childContext['containerWidth']);
    }

    public function testRenderWithBorderRadius(): void
    {
        $section = new Section(
            attributes: ['border-radius' => '10px'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $section->render();

        $this->assertStringContainsString('border-radius:10px', $html);
        $this->assertStringContainsString('overflow:hidden', $html);
    }

    public function testRenderWithDirection(): void
    {
        $section = new Section(
            attributes: ['direction' => 'rtl'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $section->render();

        $this->assertStringContainsString('direction:rtl', $html);
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
