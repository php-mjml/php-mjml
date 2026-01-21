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
use PhpMjml\Components\Body\Text;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class ColumnTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-column', Column::getComponentName());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Column::getDefaultAttributes();

        $this->assertSame('ltr', $defaults['direction']);
        $this->assertSame('top', $defaults['vertical-align']);
    }

    public function testRenderBasicColumn(): void
    {
        $column = new Column(
            attributes: [],
            children: [],
            content: '',
            context: $this->createContext(),
            props: ['nonRawSiblings' => 1],
        );

        $html = $column->render();

        $this->assertStringContainsString('<div', $html);
        $this->assertStringContainsString('mj-column-per-100', $html);
        $this->assertStringContainsString('mj-outlook-group-fix', $html);
    }

    public function testRenderWithExplicitWidth(): void
    {
        $column = new Column(
            attributes: ['width' => '50%'],
            children: [],
            content: '',
            context: $this->createContext(),
            props: ['nonRawSiblings' => 2],
        );

        $html = $column->render();

        $this->assertStringContainsString('mj-column-per-50', $html);
    }

    public function testRenderWithPadding(): void
    {
        $column = new Column(
            attributes: ['padding' => '10px'],
            children: [],
            content: '',
            context: $this->createContext(),
            props: ['nonRawSiblings' => 1],
        );

        $html = $column->render();

        // Should render gutter table
        $this->assertStringContainsString('padding:10px', $html);
    }

    public function testRenderWithBackgroundColor(): void
    {
        $column = new Column(
            attributes: ['background-color' => '#ffffff'],
            children: [],
            content: '',
            context: $this->createContext(),
            props: ['nonRawSiblings' => 1],
        );

        $html = $column->render();

        $this->assertStringContainsString('background-color:#ffffff', $html);
    }

    public function testRenderWithChildren(): void
    {
        $context = $this->createContext();

        $textChild = new Text(
            attributes: [],
            children: [],
            content: 'Column content',
            context: $context,
        );

        $column = new Column(
            attributes: [],
            children: [$textChild],
            content: '',
            context: $context,
            props: ['nonRawSiblings' => 1],
        );

        $html = $column->render();

        $this->assertStringContainsString('Column content', $html);
        $this->assertStringContainsString('<tr>', $html);
        $this->assertStringContainsString('<td', $html);
    }

    public function testGetChildContext(): void
    {
        $column = new Column(
            attributes: ['width' => '300px'],
            children: [],
            content: '',
            context: $this->createContext(600),
            props: ['nonRawSiblings' => 2],
        );

        $childContext = $column->getChildContext();

        // 300px width with no padding/borders should give ~300px container
        $this->assertLessThanOrEqual(300, $childContext['containerWidth']);
    }

    public function testMediaQueryRegistration(): void
    {
        $context = $this->createContext();

        $column = new Column(
            attributes: ['width' => '50%'],
            children: [],
            content: '',
            context: $context,
            props: ['nonRawSiblings' => 2],
        );

        $column->render();

        // Media query should be registered
        $this->assertArrayHasKey('mj-column-per-50', $context->getMediaQueries());
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
