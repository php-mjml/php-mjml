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

namespace PhpMjml\Tests\Unit\Components\Head;

use PhpMjml\Component\Registry;
use PhpMjml\Components\Head\Attributes;
use PhpMjml\Parser\Node;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class AttributesTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-attributes', Attributes::getComponentName());
    }

    public function testHandleSetsComponentDefaultAttributes(): void
    {
        $context = $this->createContext();

        $attributes = new Attributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'mj-text',
                attributes: ['color' => 'red', 'font-size' => '14px'],
            ),
        ];

        $attributes->setRawChildren($childNodes);
        $attributes->handle($context);

        $this->assertArrayHasKey('mj-text', $context->headAttributes);
        $this->assertSame('red', $context->headAttributes['mj-text']['color']);
        $this->assertSame('14px', $context->headAttributes['mj-text']['font-size']);
    }

    public function testHandleSetsAllComponentDefaultAttributes(): void
    {
        $context = $this->createContext();

        $attributes = new Attributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'mj-all',
                attributes: ['font-family' => 'Arial, sans-serif'],
            ),
        ];

        $attributes->setRawChildren($childNodes);
        $attributes->handle($context);

        $this->assertArrayHasKey(Attributes::TAG_NAME_ALL, $context->headAttributes);
        $this->assertSame('Arial, sans-serif', $context->headAttributes[Attributes::TAG_NAME_ALL]['font-family']);
    }

    public function testHandleSetsClassAttributes(): void
    {
        $context = $this->createContext();

        $attributes = new Attributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'mj-class',
                attributes: ['name' => 'blue', 'color' => 'blue'],
            ),
        ];

        $attributes->setRawChildren($childNodes);
        $attributes->handle($context);

        $this->assertArrayHasKey(Attributes::TAG_NAME_CLASS, $context->headAttributes);
        $this->assertArrayHasKey('blue', $context->headAttributes[Attributes::TAG_NAME_CLASS]);
        $this->assertSame('blue', $context->headAttributes[Attributes::TAG_NAME_CLASS]['blue']['color']);
        // The 'name' attribute should not be included in the class attributes
        $this->assertArrayNotHasKey('name', $context->headAttributes[Attributes::TAG_NAME_CLASS]['blue']);
    }

    public function testHandleIgnoresClassWithoutName(): void
    {
        $context = $this->createContext();

        $attributes = new Attributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'mj-class',
                attributes: ['color' => 'blue'], // Missing 'name' attribute
            ),
        ];

        $attributes->setRawChildren($childNodes);
        $attributes->handle($context);

        // Class without name should be ignored
        $this->assertArrayNotHasKey(Attributes::TAG_NAME_CLASS, $context->headAttributes);
    }

    public function testHandleMultipleComponents(): void
    {
        $context = $this->createContext();

        $attributes = new Attributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'mj-all',
                attributes: ['font-family' => 'Arial'],
            ),
            new Node(
                tagName: 'mj-text',
                attributes: ['color' => 'red'],
            ),
            new Node(
                tagName: 'mj-button',
                attributes: ['background-color' => 'blue'],
            ),
            new Node(
                tagName: 'mj-class',
                attributes: ['name' => 'centered', 'align' => 'center'],
            ),
        ];

        $attributes->setRawChildren($childNodes);
        $attributes->handle($context);

        $this->assertArrayHasKey(Attributes::TAG_NAME_ALL, $context->headAttributes);
        $this->assertArrayHasKey('mj-text', $context->headAttributes);
        $this->assertArrayHasKey('mj-button', $context->headAttributes);
        $this->assertArrayHasKey(Attributes::TAG_NAME_CLASS, $context->headAttributes);
        $this->assertArrayHasKey('centered', $context->headAttributes[Attributes::TAG_NAME_CLASS]);
    }

    public function testHandleMergesAttributesForSameComponent(): void
    {
        $context = $this->createContext();

        $attributes = new Attributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'mj-text',
                attributes: ['color' => 'red'],
            ),
            new Node(
                tagName: 'mj-text',
                attributes: ['font-size' => '16px'],
            ),
        ];

        $attributes->setRawChildren($childNodes);
        $attributes->handle($context);

        $this->assertArrayHasKey('mj-text', $context->headAttributes);
        $this->assertSame('red', $context->headAttributes['mj-text']['color']);
        $this->assertSame('16px', $context->headAttributes['mj-text']['font-size']);
    }

    public function testHandleNestedClassDefaults(): void
    {
        $context = $this->createContext();

        $attributes = new Attributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        // mj-class with nested component-specific defaults
        $childNodes = [
            new Node(
                tagName: 'mj-class',
                attributes: ['name' => 'blue', 'color' => 'blue'],
                children: [
                    new Node(
                        tagName: 'mj-button',
                        attributes: ['background-color' => 'navy'],
                    ),
                ],
            ),
        ];

        $attributes->setRawChildren($childNodes);
        $attributes->handle($context);

        $this->assertArrayHasKey(Attributes::TAG_NAME_CLASS, $context->headAttributes);
        $this->assertArrayHasKey('blue', $context->headAttributes[Attributes::TAG_NAME_CLASS]);
        $this->assertSame('blue', $context->headAttributes[Attributes::TAG_NAME_CLASS]['blue']['color']);
        $this->assertArrayHasKey('__defaults', $context->headAttributes[Attributes::TAG_NAME_CLASS]['blue']);
        $this->assertArrayHasKey('mj-button', $context->headAttributes[Attributes::TAG_NAME_CLASS]['blue']['__defaults']);
        $this->assertSame('navy', $context->headAttributes[Attributes::TAG_NAME_CLASS]['blue']['__defaults']['mj-button']['background-color']);
    }

    public function testRenderReturnsEmptyString(): void
    {
        $attributes = new Attributes(
            attributes: [],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $this->assertSame('', $attributes->render());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Attributes::getDefaultAttributes();

        $this->assertSame([], $defaults);
    }

    public function testAllowedAttributes(): void
    {
        $allowed = Attributes::getAllowedAttributes();

        $this->assertSame([], $allowed);
    }

    public function testConstants(): void
    {
        $this->assertSame('mj-all', Attributes::TAG_NAME_ALL);
        $this->assertSame('mj-class', Attributes::TAG_NAME_CLASS);
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
