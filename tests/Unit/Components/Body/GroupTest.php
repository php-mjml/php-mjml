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
use PhpMjml\Components\Body\Group;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class GroupTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-group', Group::getComponentName());
    }

    public function testIsNotEndingTag(): void
    {
        $this->assertFalse(Group::isEndingTag());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Group::getDefaultAttributes();

        $this->assertSame('ltr', $defaults['direction']);
    }

    public function testAllowedAttributes(): void
    {
        $allowed = Group::getAllowedAttributes();

        $this->assertArrayHasKey('background-color', $allowed);
        $this->assertArrayHasKey('direction', $allowed);
        $this->assertArrayHasKey('vertical-align', $allowed);
        $this->assertArrayHasKey('width', $allowed);
    }

    public function testRenderBasicGroup(): void
    {
        $group = new Group(
            attributes: [],
            children: [],
            content: '',
            context: $this->createContext(),
            props: ['nonRawSiblings' => 1],
        );

        $html = $group->render();

        $this->assertStringContainsString('<div', $html);
        $this->assertStringContainsString('mj-column-per-100', $html);
        $this->assertStringContainsString('mj-outlook-group-fix', $html);
        $this->assertStringContainsString('<!--[if mso | IE]>', $html);
    }

    public function testRenderWithExplicitWidth(): void
    {
        $group = new Group(
            attributes: ['width' => '50%'],
            children: [],
            content: '',
            context: $this->createContext(),
            props: ['nonRawSiblings' => 2],
        );

        $html = $group->render();

        $this->assertStringContainsString('mj-column-per-50', $html);
    }

    public function testRenderWithDirection(): void
    {
        $group = new Group(
            attributes: ['direction' => 'rtl'],
            children: [],
            content: '',
            context: $this->createContext(),
            props: ['nonRawSiblings' => 1],
        );

        $html = $group->render();

        $this->assertStringContainsString('direction:rtl', $html);
    }

    public function testRenderWithBackgroundColor(): void
    {
        $group = new Group(
            attributes: ['background-color' => '#ff0000'],
            children: [],
            content: '',
            context: $this->createContext(),
            props: ['nonRawSiblings' => 1],
        );

        $html = $group->render();

        $this->assertStringContainsString('background-color:#ff0000', $html);
        $this->assertStringContainsString('bgcolor="#ff0000"', $html);
    }

    public function testRenderWithVerticalAlign(): void
    {
        $group = new Group(
            attributes: ['vertical-align' => 'middle'],
            children: [],
            content: '',
            context: $this->createContext(),
            props: ['nonRawSiblings' => 1],
        );

        $html = $group->render();

        $this->assertStringContainsString('vertical-align:middle', $html);
    }

    public function testRenderWithChildren(): void
    {
        $context = $this->createContext();

        $column = new Column(
            attributes: [],
            children: [],
            content: '',
            context: $context,
            props: ['nonRawSiblings' => 2],
        );

        $group = new Group(
            attributes: [],
            children: [$column],
            content: '',
            context: $context,
            props: ['nonRawSiblings' => 1],
        );

        $html = $group->render();

        $this->assertStringContainsString('<div', $html);
        $this->assertStringContainsString('<!--[if mso | IE]>', $html);
        $this->assertStringContainsString('<td', $html);
    }

    public function testGetChildContext(): void
    {
        $group = new Group(
            attributes: ['width' => '600px'],
            children: [],
            content: '',
            context: $this->createContext(600),
            props: ['nonRawSiblings' => 1],
        );

        $childContext = $group->getChildContext();

        $this->assertArrayHasKey('containerWidth', $childContext);
        $this->assertArrayHasKey('nonRawSiblings', $childContext);
        $this->assertLessThanOrEqual(600, $childContext['containerWidth']);
    }

    public function testGetChildContextWithPercentageWidth(): void
    {
        $group = new Group(
            attributes: ['width' => '50%'],
            children: [],
            content: '',
            context: $this->createContext(600),
            props: ['nonRawSiblings' => 2],
        );

        $childContext = $group->getChildContext();

        // 50% of 600px = 300px
        $this->assertLessThanOrEqual(300, $childContext['containerWidth']);
    }

    public function testMediaQueryRegistration(): void
    {
        $context = $this->createContext();

        $group = new Group(
            attributes: ['width' => '50%'],
            children: [],
            content: '',
            context: $context,
            props: ['nonRawSiblings' => 2],
        );

        $group->render();

        // Media query should be registered
        $this->assertArrayHasKey('mj-column-per-50', $context->getMediaQueries());
    }

    public function testRenderWithCssClass(): void
    {
        $group = new Group(
            attributes: ['css-class' => 'custom-group'],
            children: [],
            content: '',
            context: $this->createContext(),
            props: ['nonRawSiblings' => 1],
        );

        $html = $group->render();

        $this->assertStringContainsString('custom-group', $html);
    }

    public function testBackgroundColorNoneIsNotRendered(): void
    {
        $group = new Group(
            attributes: ['background-color' => 'none'],
            children: [],
            content: '',
            context: $this->createContext(),
            props: ['nonRawSiblings' => 1],
        );

        $html = $group->render();

        $this->assertStringNotContainsString('bgcolor=', $html);
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
