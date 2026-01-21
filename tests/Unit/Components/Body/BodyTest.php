<?php

declare(strict_types=1);

namespace PhpMjml\Tests\Unit\Components\Body;

use PHPUnit\Framework\TestCase;
use PhpMjml\Components\Body\Body;
use PhpMjml\Components\Body\Text;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PhpMjml\Component\Registry;

final class BodyTest extends TestCase
{
    private function createContext(): RenderContext
    {
        return new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
        );
    }

    public function testGetComponentName(): void
    {
        $this->assertSame('mj-body', Body::getComponentName());
    }

    public function testDefaultWidth(): void
    {
        $defaults = Body::getDefaultAttributes();

        $this->assertSame('600px', $defaults['width']);
    }

    public function testRenderEmptyBody(): void
    {
        $body = new Body(
            attributes: [],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $body->render();

        $this->assertStringContainsString('<div', $html);
        $this->assertStringContainsString('role="article"', $html);
        $this->assertStringContainsString('aria-roledescription="email"', $html);
    }

    public function testRenderWithBackgroundColor(): void
    {
        $body = new Body(
            attributes: ['background-color' => '#f4f4f4'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $html = $body->render();

        $this->assertStringContainsString('background-color:#f4f4f4', $html);
    }

    public function testGetChildContext(): void
    {
        $body = new Body(
            attributes: ['width' => '800px'],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        $childContext = $body->getChildContext();

        $this->assertSame(800, $childContext['containerWidth']);
    }

    public function testRenderWithChildren(): void
    {
        $context = $this->createContext();

        $textChild = new Text(
            attributes: [],
            children: [],
            content: 'Child content',
            context: $context,
        );

        $body = new Body(
            attributes: [],
            children: [$textChild],
            content: '',
            context: $context,
        );

        $html = $body->render();

        $this->assertStringContainsString('Child content', $html);
    }

    public function testRenderWithTitle(): void
    {
        $context = new RenderContext(
            registry: new Registry(),
            options: new RenderOptions(),
            containerWidth: 600,
            title: 'Email Title',
        );

        $body = new Body(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $html = $body->render();

        $this->assertStringContainsString('aria-label="Email Title"', $html);
    }
}
