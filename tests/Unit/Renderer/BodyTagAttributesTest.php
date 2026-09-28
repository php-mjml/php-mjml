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

use PhpMjml\Renderer\Mjml2Html;
use PHPUnit\Framework\TestCase;

/**
 * Tests that mj-body id and css-class are rendered on the <body> tag.
 */
final class BodyTagAttributesTest extends TestCase
{
    private Mjml2Html $renderer;

    protected function setUp(): void
    {
        $this->renderer = Mjml2Html::create();
    }

    public function testCssClassIsRenderedOnBodyTag(): void
    {
        $html = $this->renderer->render($this->mjml('<mj-body css-class="wrap">'))->html;

        $this->assertStringContainsString('<body class="wrap" style="word-spacing:normal;">', $html);
        $this->assertDoesNotMatchRegularExpression('/<div[^>]*class="wrap"[^>]*role="article"/', $html);
    }

    public function testIdIsRenderedOnBodyTag(): void
    {
        $result = $this->renderer->render($this->mjml('<mj-body id="main">'));

        $this->assertStringContainsString('<body id="main" style="word-spacing:normal;">', $result->html);
        $this->assertSame([], $result->errors);
    }

    public function testBodyTagAttributesAreEscaped(): void
    {
        $html = $this->renderer->render($this->mjml('<mj-body css-class="a&quot;b">'))->html;

        $this->assertStringContainsString('<body class="a&quot;b"', $html);
    }

    public function testBodyTagHasOnlyStyleByDefault(): void
    {
        $html = $this->renderer->render($this->mjml('<mj-body>'))->html;

        $this->assertStringContainsString('<body style="word-spacing:normal;">', $html);
    }

    private function mjml(string $bodyTag): string
    {
        return '<mjml>'.$bodyTag.'<mj-section><mj-column><mj-text>Hi</mj-text></mj-column></mj-section></mj-body></mjml>';
    }
}
