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
use PhpMjml\Components\Head\Breakpoint;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class BreakpointTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-breakpoint', Breakpoint::getComponentName());
    }

    public function testHandleSetsBreakpointInContext(): void
    {
        $context = $this->createContext();
        $breakpointWidth = '600px';

        $breakpoint = new Breakpoint(
            attributes: ['width' => $breakpointWidth],
            children: [],
            content: '',
            context: $context,
        );

        $breakpoint->handle($context);

        $this->assertSame($breakpointWidth, $context->breakpoint);
    }

    public function testHandleWithMissingWidthDoesNotChangeBreakpoint(): void
    {
        $context = $this->createContext();
        $defaultBreakpoint = $context->breakpoint;

        $breakpoint = new Breakpoint(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $breakpoint->handle($context);

        $this->assertSame($defaultBreakpoint, $context->breakpoint);
    }

    public function testRenderReturnsEmptyString(): void
    {
        $breakpoint = new Breakpoint(
            attributes: ['width' => '500px'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $this->assertSame('', $breakpoint->render());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Breakpoint::getDefaultAttributes();

        $this->assertSame([], $defaults);
    }

    public function testAllowedAttributes(): void
    {
        $allowed = Breakpoint::getAllowedAttributes();

        $this->assertSame(['width' => 'unit(px)'], $allowed);
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
