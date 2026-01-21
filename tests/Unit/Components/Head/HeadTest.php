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
use PhpMjml\Components\Head\Head;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class HeadTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-head', Head::getComponentName());
    }

    public function testHandleDoesNotModifyContext(): void
    {
        $context = $this->createContext();
        $originalTitle = $context->title;
        $originalPreview = $context->preview;

        $head = new Head(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $head->handle($context);

        // mj-head is a container only, it should not modify context
        $this->assertSame($originalTitle, $context->title);
        $this->assertSame($originalPreview, $context->preview);
    }

    public function testRenderReturnsEmptyString(): void
    {
        $head = new Head(
            attributes: [],
            children: [],
            content: '',
            context: $this->createContext(),
        );

        // HeadComponent::render() always returns empty string
        $this->assertSame('', $head->render());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Head::getDefaultAttributes();

        // mj-head has no default attributes
        $this->assertSame([], $defaults);
    }

    public function testAllowedAttributes(): void
    {
        $allowed = Head::getAllowedAttributes();

        // mj-head has no allowed attributes
        $this->assertSame([], $allowed);
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
