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

namespace PhpMjml\Tests\Unit\Renderer;

use PhpMjml\Component\Registry;
use PhpMjml\Renderer\RenderContext;
use PhpMjml\Renderer\RenderOptions;
use PHPUnit\Framework\TestCase;

final class RenderContextTest extends TestCase
{
    public function testChildContextPreservesSettingsAndSharesGlobalData(): void
    {
        $parent = new RenderContext(new Registry(), new RenderOptions(), [
            'title' => 'Parent',
            'fonts' => ['Custom' => 'https://example.com/font.css'],
            'inheritedAttributes' => ['color' => 'red'],
            'componentData' => ['accordion' => ['fontFamily' => 'Arial']],
        ]);
        $parent->setPreview('Preview');
        $parent->breakpoint = '640px';

        $child = RenderContext::fromArray(['containerWidth' => 300], $parent);
        $child->setTitle('Child');
        $child->globalData->addStyle('.shared { color: red; }');

        $this->assertSame('Parent', $parent->getTitle());
        $this->assertSame('Child', $child->title);
        $this->assertSame('Preview', $child->getPreview());
        $this->assertSame('640px', $child->getBreakpoint());
        $this->assertSame(300, $child->getContainerWidth());
        $this->assertSame($parent->getFonts(), $child->getFonts());
        $this->assertSame(['fontFamily' => 'Arial'], $child->getComponentData('accordion'));
        $this->assertSame([], $child->inheritedAttributes);
        $this->assertSame(['color' => 'red'], $parent->inheritedAttributes);
        $this->assertSame($parent->globalData, $child->globalData);
        $this->assertSame(['.shared { color: red; }'], $parent->getStyles());
        $this->assertSame($child->toArray(), RenderContext::fromArray($child->toArray(), $parent)->toArray());
    }

    public function testNamedClassMetadataIsNotAComponentDefault(): void
    {
        $context = new RenderContext(new Registry(), new RenderOptions(), [
            'headAttributes' => [
                'mj-text' => ['color' => 'red'],
                'mj-class' => ['blue' => ['color' => 'blue', '__defaults' => ['mj-button' => ['color' => 'navy']]]],
            ],
        ]);

        $this->assertSame(['color' => 'red'], $context->getDefaultAttributes('mj-text'));
        $this->assertSame([], $context->getDefaultAttributes('mj-class'));
        $this->assertSame([], $context->getDefaultAttributes('mj-image'));
    }
}
