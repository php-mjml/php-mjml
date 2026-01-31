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
use PhpMjml\Components\Head\HtmlAttributes;
use PhpMjml\Parser\Node;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class HtmlAttributesTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-html-attributes', HtmlAttributes::getComponentName());
    }

    public function testHandleSetsHtmlAttributesForSelector(): void
    {
        $context = $this->createContext();

        $htmlAttributes = new HtmlAttributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'mj-selector',
                attributes: ['path' => '.custom div'],
                children: [
                    new Node(
                        tagName: 'mj-html-attribute',
                        attributes: ['name' => 'data-id'],
                        content: '42',
                    ),
                ],
            ),
        ];

        $htmlAttributes->setRawChildren($childNodes);
        $htmlAttributes->handle($context);

        $this->assertArrayHasKey('.custom div', $context->globalData->htmlAttributes);
        $this->assertSame('42', $context->globalData->htmlAttributes['.custom div']['data-id']);
    }

    public function testHandleMultipleAttributesPerSelector(): void
    {
        $context = $this->createContext();

        $htmlAttributes = new HtmlAttributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'mj-selector',
                attributes: ['path' => '.text div'],
                children: [
                    new Node(
                        tagName: 'mj-html-attribute',
                        attributes: ['name' => 'data-id'],
                        content: '42',
                    ),
                    new Node(
                        tagName: 'mj-html-attribute',
                        attributes: ['name' => 'data-name'],
                        content: 'test',
                    ),
                ],
            ),
        ];

        $htmlAttributes->setRawChildren($childNodes);
        $htmlAttributes->handle($context);

        $this->assertArrayHasKey('.text div', $context->globalData->htmlAttributes);
        $this->assertSame('42', $context->globalData->htmlAttributes['.text div']['data-id']);
        $this->assertSame('test', $context->globalData->htmlAttributes['.text div']['data-name']);
    }

    public function testHandleMultipleSelectors(): void
    {
        $context = $this->createContext();

        $htmlAttributes = new HtmlAttributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'mj-selector',
                attributes: ['path' => '.text div'],
                children: [
                    new Node(
                        tagName: 'mj-html-attribute',
                        attributes: ['name' => 'data-id'],
                        content: '42',
                    ),
                ],
            ),
            new Node(
                tagName: 'mj-selector',
                attributes: ['path' => '.image td'],
                children: [
                    new Node(
                        tagName: 'mj-html-attribute',
                        attributes: ['name' => 'data-name'],
                        content: '43',
                    ),
                ],
            ),
        ];

        $htmlAttributes->setRawChildren($childNodes);
        $htmlAttributes->handle($context);

        $this->assertArrayHasKey('.text div', $context->globalData->htmlAttributes);
        $this->assertArrayHasKey('.image td', $context->globalData->htmlAttributes);
        $this->assertSame('42', $context->globalData->htmlAttributes['.text div']['data-id']);
        $this->assertSame('43', $context->globalData->htmlAttributes['.image td']['data-name']);
    }

    public function testHandleIgnoresSelectorWithoutPath(): void
    {
        $context = $this->createContext();

        $htmlAttributes = new HtmlAttributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'mj-selector',
                attributes: [], // Missing path attribute
                children: [
                    new Node(
                        tagName: 'mj-html-attribute',
                        attributes: ['name' => 'data-id'],
                        content: '42',
                    ),
                ],
            ),
        ];

        $htmlAttributes->setRawChildren($childNodes);
        $htmlAttributes->handle($context);

        $this->assertSame([], $context->globalData->htmlAttributes);
    }

    public function testHandleIgnoresAttributeWithoutName(): void
    {
        $context = $this->createContext();

        $htmlAttributes = new HtmlAttributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'mj-selector',
                attributes: ['path' => '.custom div'],
                children: [
                    new Node(
                        tagName: 'mj-html-attribute',
                        attributes: [], // Missing name attribute
                        content: '42',
                    ),
                ],
            ),
        ];

        $htmlAttributes->setRawChildren($childNodes);
        $htmlAttributes->handle($context);

        $this->assertArrayNotHasKey('.custom div', $context->globalData->htmlAttributes);
    }

    public function testHandleIgnoresNonSelectorChildren(): void
    {
        $context = $this->createContext();

        $htmlAttributes = new HtmlAttributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'some-other-tag',
                attributes: ['path' => '.custom div'],
                children: [
                    new Node(
                        tagName: 'mj-html-attribute',
                        attributes: ['name' => 'data-id'],
                        content: '42',
                    ),
                ],
            ),
        ];

        $htmlAttributes->setRawChildren($childNodes);
        $htmlAttributes->handle($context);

        $this->assertSame([], $context->globalData->htmlAttributes);
    }

    public function testHandleIgnoresNonAttributeChildren(): void
    {
        $context = $this->createContext();

        $htmlAttributes = new HtmlAttributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'mj-selector',
                attributes: ['path' => '.custom div'],
                children: [
                    new Node(
                        tagName: 'other-tag',
                        attributes: ['name' => 'data-id'],
                        content: '42',
                    ),
                ],
            ),
        ];

        $htmlAttributes->setRawChildren($childNodes);
        $htmlAttributes->handle($context);

        $this->assertArrayNotHasKey('.custom div', $context->globalData->htmlAttributes);
    }

    public function testHandleEmptyContentValue(): void
    {
        $context = $this->createContext();

        $htmlAttributes = new HtmlAttributes(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $childNodes = [
            new Node(
                tagName: 'mj-selector',
                attributes: ['path' => '.custom div'],
                children: [
                    new Node(
                        tagName: 'mj-html-attribute',
                        attributes: ['name' => 'data-empty'],
                        content: '',
                    ),
                ],
            ),
        ];

        $htmlAttributes->setRawChildren($childNodes);
        $htmlAttributes->handle($context);

        $this->assertArrayHasKey('.custom div', $context->globalData->htmlAttributes);
        $this->assertSame('', $context->globalData->htmlAttributes['.custom div']['data-empty']);
    }

    public function testRenderReturnsEmptyString(): void
    {
        $htmlAttributes = new HtmlAttributes(
            attributes: [],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $this->assertSame('', $htmlAttributes->render());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = HtmlAttributes::getDefaultAttributes();

        $this->assertSame([], $defaults);
    }

    public function testAllowedAttributes(): void
    {
        $allowed = HtmlAttributes::getAllowedAttributes();

        $this->assertSame([], $allowed);
    }

    public function testConstants(): void
    {
        $this->assertSame('mj-selector', HtmlAttributes::TAG_NAME_SELECTOR);
        $this->assertSame('mj-html-attribute', HtmlAttributes::TAG_NAME_ATTRIBUTE);
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
