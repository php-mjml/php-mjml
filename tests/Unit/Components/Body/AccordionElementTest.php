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
use PhpMjml\Components\Body\AccordionElement;
use PhpMjml\Components\Body\AccordionText;
use PhpMjml\Components\Body\AccordionTitle;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class AccordionElementTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-accordion-element', AccordionElement::getComponentName());
    }

    public function testIsEndingTag(): void
    {
        $this->assertFalse(AccordionElement::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = AccordionElement::getDefaultAttributes();

        $this->assertEmpty($defaults);
    }

    public function testRenderBasic(): void
    {
        $context = $this->createContext();

        $element = new AccordionElement(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $html = $element->render();

        $this->assertStringContainsString('<tr', $html);
        $this->assertStringContainsString('<td', $html);
        $this->assertStringContainsString('<label', $html);
        $this->assertStringContainsString('class="mj-accordion-element"', $html);
        $this->assertStringContainsString('mj-accordion-checkbox', $html);
        // Should include default title and text when children are missing
        $this->assertStringContainsString('mj-accordion-title', $html);
        $this->assertStringContainsString('mj-accordion-content', $html);
    }

    public function testRenderWithTitle(): void
    {
        $context = $this->createContext();

        $title = new AccordionTitle(
            attributes: [],
            children: [],
            content: 'Test Title',
            context: $context,
        );

        $element = new AccordionElement(
            attributes: [],
            children: [$title],
            content: '',
            context: $context,
        );

        $html = $element->render();

        $this->assertStringContainsString('Test Title', $html);
        // Should still add default text since it's missing
        $this->assertStringContainsString('mj-accordion-content', $html);
    }

    public function testRenderWithTitleAndText(): void
    {
        $context = $this->createContext();

        $title = new AccordionTitle(
            attributes: [],
            children: [],
            content: 'My Title',
            context: $context,
        );

        $text = new AccordionText(
            attributes: [],
            children: [],
            content: 'My Content',
            context: $context,
        );

        $element = new AccordionElement(
            attributes: [],
            children: [$title, $text],
            content: '',
            context: $context,
        );

        $html = $element->render();

        $this->assertStringContainsString('My Title', $html);
        $this->assertStringContainsString('My Content', $html);
    }

    public function testRenderWithBackgroundColor(): void
    {
        $context = $this->createContext();

        $element = new AccordionElement(
            attributes: ['background-color' => '#f0f0f0'],
            children: [],
            content: '',
            context: $context,
        );

        $html = $element->render();

        $this->assertStringContainsString('background-color:#f0f0f0', $html);
    }

    public function testGetChildContext(): void
    {
        $context = $this->createContext();

        $element = new AccordionElement(
            attributes: [
                'font-family' => 'Georgia, serif',
                'icon-position' => 'left',
            ],
            children: [],
            content: '',
            context: $context,
        );

        $childContext = $element->getChildContext();

        $this->assertArrayHasKey('componentData', $childContext);
        $this->assertArrayHasKey('accordion', $childContext['componentData']);
        $this->assertSame('Georgia, serif', $childContext['componentData']['accordion']['elementFontFamily']);
        $this->assertSame('left', $childContext['componentData']['accordion']['iconPosition']);
    }

    public function testGetStyles(): void
    {
        $context = $this->createContext();

        $element = new AccordionElement(
            attributes: ['background-color' => '#ffffff'],
            children: [],
            content: '',
            context: $context,
        );

        $styles = $element->getStyles();

        $this->assertArrayHasKey('td', $styles);
        $this->assertArrayHasKey('label', $styles);
        $this->assertArrayHasKey('input', $styles);
        $this->assertSame('0px', $styles['td']['padding']);
        $this->assertSame('#ffffff', $styles['td']['background-color']);
        $this->assertSame('none', $styles['input']['display']);
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
