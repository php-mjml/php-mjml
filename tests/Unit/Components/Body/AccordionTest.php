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
use PhpMjml\Components\Body\Accordion;
use PhpMjml\Components\Body\AccordionElement;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class AccordionTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-accordion', Accordion::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertFalse(Accordion::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Accordion::getDefaultAttributes();

        $this->assertSame('2px solid black', $defaults['border']);
        $this->assertSame('Ubuntu, Helvetica, Arial, sans-serif', $defaults['font-family']);
        $this->assertSame('middle', $defaults['icon-align']);
        $this->assertSame('https://i.imgur.com/bIXv1bk.png', $defaults['icon-wrapped-url']);
        $this->assertSame('+', $defaults['icon-wrapped-alt']);
        $this->assertSame('https://i.imgur.com/w4uTygT.png', $defaults['icon-unwrapped-url']);
        $this->assertSame('-', $defaults['icon-unwrapped-alt']);
        $this->assertSame('right', $defaults['icon-position']);
        $this->assertSame('32px', $defaults['icon-height']);
        $this->assertSame('32px', $defaults['icon-width']);
        $this->assertSame('10px 25px', $defaults['padding']);
    }

    public function testRenderBasic(): void
    {
        $context = $this->createContext();

        $accordion = new Accordion(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $html = $accordion->render();

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('class="mj-accordion"', $html);
        $this->assertStringContainsString('<tbody>', $html);
        $this->assertStringContainsString('border-collapse:collapse', $html);
    }

    public function testRenderWithChildren(): void
    {
        $context = $this->createContext();

        $element = new AccordionElement(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $accordion = new Accordion(
            attributes: [],
            children: [$element],
            content: '',
            context: $context,
        );

        $html = $accordion->render();

        $this->assertStringContainsString('mj-accordion-element', $html);
    }

    public function testGetChildContext(): void
    {
        $context = $this->createContext();

        $accordion = new Accordion(
            attributes: [
                'font-family' => 'Arial, sans-serif',
                'border' => '1px solid red',
                'icon-position' => 'left',
            ],
            children: [],
            content: '',
            context: $context,
        );

        $childContext = $accordion->getChildContext();

        $this->assertArrayHasKey('accordionSettings', $childContext);
        $this->assertSame('Arial, sans-serif', $childContext['accordionSettings']['fontFamily']);
        $this->assertSame('1px solid red', $childContext['accordionSettings']['border']);
        $this->assertSame('left', $childContext['accordionSettings']['iconPosition']);
    }

    public function testGetStyles(): void
    {
        $context = $this->createContext();

        $accordion = new Accordion(
            attributes: ['border' => '1px solid blue'],
            children: [],
            content: '',
            context: $context,
        );

        $styles = $accordion->getStyles();

        $this->assertArrayHasKey('table', $styles);
        $this->assertSame('100%', $styles['table']['width']);
        $this->assertSame('collapse', $styles['table']['border-collapse']);
        $this->assertSame('1px solid blue', $styles['table']['border']);
        $this->assertSame('none', $styles['table']['border-bottom']);
    }

    public function testHeadStyleIsAdded(): void
    {
        $context = $this->createContext();

        $accordion = new Accordion(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $accordion->render();

        $headStyles = $context->globalData->headStyle;
        $this->assertArrayHasKey('mj-accordion', $headStyles);

        $style = $headStyles['mj-accordion'];
        $this->assertStringContainsString('mj-accordion-checkbox', $style);
        $this->assertStringContainsString('mj-accordion-element', $style);
        $this->assertStringContainsString('mj-accordion-content', $style);
        $this->assertStringContainsString('@media yahoo', $style);
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
