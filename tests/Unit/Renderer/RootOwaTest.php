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
 * Tests the owa attribute on the root <mjml> tag.
 */
final class RootOwaTest extends TestCase
{
    private Mjml2Html $renderer;

    protected function setUp(): void
    {
        $this->renderer = Mjml2Html::create();
    }

    public function testOwaDesktopEmitsOwaMediaQueries(): void
    {
        $html = $this->renderer->render($this->mjml('<mjml owa="desktop">'))->html;

        $this->assertStringContainsString(
            '[owa] .mj-column-per-50 { width:50% !important; max-width: 50%; }',
            $html
        );
    }

    public function testOwaDefaultsToMobile(): void
    {
        $html = $this->renderer->render($this->mjml('<mjml>'))->html;

        $this->assertStringNotContainsString('[owa]', $html);
    }

    public function testOwaMobileDoesNotEmitOwaMediaQueries(): void
    {
        $html = $this->renderer->render($this->mjml('<mjml owa="mobile">'))->html;

        $this->assertStringNotContainsString('[owa]', $html);
    }

    private function mjml(string $rootTag): string
    {
        return $rootTag.'<mj-body><mj-section><mj-column><mj-text>A</mj-text></mj-column><mj-column><mj-text>B</mj-text></mj-column></mj-section></mj-body></mjml>';
    }
}
