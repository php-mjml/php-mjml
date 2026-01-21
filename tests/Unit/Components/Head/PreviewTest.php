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
use PhpMjml\Components\Head\Preview;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class PreviewTest extends TestCase
{
    public function testGetComponentName(): void
    {
        $this->assertSame('mj-preview', Preview::getComponentName());
    }

    public function testHandleSetsContextPreview(): void
    {
        $context = $this->createContext();
        $previewText = 'This is preview text for email clients';

        $preview = new Preview(
            attributes: [],
            children: [],
            content: $previewText,
            context: $context,
        );

        $preview->handle($context);

        $this->assertSame($previewText, $context->preview);
    }

    public function testHandleWithEmptyContent(): void
    {
        $context = $this->createContext();

        $preview = new Preview(
            attributes: [],
            children: [],
            content: '',
            context: $context,
        );

        $preview->handle($context);

        $this->assertSame('', $context->preview);
    }

    public function testRenderReturnsEmptyString(): void
    {
        $preview = new Preview(
            attributes: [],
            children: [],
            content: 'Preview text',
            context: $this->createContext(),
        );

        $this->assertSame('', $preview->render());
    }

    public function testDefaultAttributes(): void
    {
        $defaults = Preview::getDefaultAttributes();

        $this->assertSame([], $defaults);
    }

    public function testAllowedAttributes(): void
    {
        $allowed = Preview::getAllowedAttributes();

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
