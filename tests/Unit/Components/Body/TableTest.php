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
use PhpMjml\Components\Body\Table;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class TableTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-table', Table::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertTrue(Table::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Table::getDefaultAttributes();

        $this->assertSame('left', $defaults['align']);
        $this->assertSame('none', $defaults['border']);
        $this->assertSame('0', $defaults['cellpadding']);
        $this->assertSame('0', $defaults['cellspacing']);
        $this->assertSame('#000000', $defaults['color']);
        $this->assertSame('Ubuntu, Helvetica, Arial, sans-serif', $defaults['font-family']);
        $this->assertSame('13px', $defaults['font-size']);
        $this->assertSame('22px', $defaults['line-height']);
        $this->assertSame('10px 25px', $defaults['padding']);
        $this->assertSame('auto', $defaults['table-layout']);
        $this->assertSame('100%', $defaults['width']);
    }

    public function testRenderBasicTable(): void
    {
        $table = new Table(
            attributes: [],
            children: [],
            content: '<tr><td>Cell</td></tr>',
            context: $this->createContext(),
        );

        $html = $table->render();

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('</table>', $html);
        $this->assertStringContainsString('<tr><td>Cell</td></tr>', $html);
        $this->assertStringContainsString('cellpadding="0"', $html);
        $this->assertStringContainsString('cellspacing="0"', $html);
        $this->assertStringContainsString('border="0"', $html);
        $this->assertStringContainsString('width="100%"', $html);
    }

    public function testRenderWithCustomWidth(): void
    {
        $table = new Table(
            attributes: ['width' => '400px'],
            children: [],
            content: '<tr><td>Cell</td></tr>',
            context: $this->createContext(),
        );

        $html = $table->render();

        $this->assertStringContainsString('width="400"', $html);
    }

    public function testRenderWithPercentWidth(): void
    {
        $table = new Table(
            attributes: ['width' => '50%'],
            children: [],
            content: '<tr><td>Cell</td></tr>',
            context: $this->createContext(),
        );

        $html = $table->render();

        $this->assertStringContainsString('width="50%"', $html);
    }

    public function testRenderWithAutoWidth(): void
    {
        $table = new Table(
            attributes: ['width' => 'auto'],
            children: [],
            content: '<tr><td>Cell</td></tr>',
            context: $this->createContext(),
        );

        $html = $table->render();

        $this->assertStringContainsString('width="auto"', $html);
    }

    public function testRenderWithRole(): void
    {
        $table = new Table(
            attributes: ['role' => 'presentation'],
            children: [],
            content: '<tr><td>Cell</td></tr>',
            context: $this->createContext(),
        );

        $html = $table->render();

        $this->assertStringContainsString('role="presentation"', $html);
    }

    public function testGetStyles(): void
    {
        $table = new Table(
            attributes: [
                'color' => '#333333',
                'font-family' => 'Arial',
                'font-size' => '14px',
                'line-height' => '20px',
                'table-layout' => 'fixed',
                'border' => '1px solid black',
            ],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $styles = $table->getStyles();

        $this->assertArrayHasKey('table', $styles);
        $this->assertSame('#333333', $styles['table']['color']);
        $this->assertSame('Arial', $styles['table']['font-family']);
        $this->assertSame('14px', $styles['table']['font-size']);
        $this->assertSame('20px', $styles['table']['line-height']);
        $this->assertSame('fixed', $styles['table']['table-layout']);
        $this->assertSame('1px solid black', $styles['table']['border']);
    }

    public function testGetStylesWithCellspacing(): void
    {
        $table = new Table(
            attributes: ['cellspacing' => '10'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $styles = $table->getStyles();

        $this->assertArrayHasKey('border-collapse', $styles['table']);
        $this->assertSame('separate', $styles['table']['border-collapse']);
    }

    public function testGetStylesWithoutCellspacing(): void
    {
        $table = new Table(
            attributes: ['cellspacing' => '0'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $styles = $table->getStyles();

        $this->assertArrayNotHasKey('border-collapse', $styles['table']);
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
