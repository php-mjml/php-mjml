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
 * Tests that lang and dir attributes on the root <mjml> tag reach the output.
 *
 * @see https://github.com/php-mjml/php-mjml/issues/13
 */
final class RootLangDirTest extends TestCase
{
    private Mjml2Html $renderer;

    protected function setUp(): void
    {
        $this->renderer = Mjml2Html::create();
    }

    public function testDefaultsToUndAndAuto(): void
    {
        $html = $this->renderer->render($this->mjml('<mjml>'))->html;

        $this->assertStringContainsString('<html lang="und" dir="auto"', $html);
        $this->assertMatchesRegularExpression('/<div[^>]*role="article"[^>]*lang="und"[^>]*dir="auto"/', $html);
    }

    public function testRootLangIsAppliedToHtmlTag(): void
    {
        $html = $this->renderer->render($this->mjml('<mjml lang="nl">'))->html;

        $this->assertStringContainsString('<html lang="nl" dir="auto"', $html);
    }

    public function testRootDirIsAppliedToHtmlTag(): void
    {
        $html = $this->renderer->render($this->mjml('<mjml dir="rtl">'))->html;

        $this->assertStringContainsString('<html lang="und" dir="rtl"', $html);
    }

    public function testRootLangAndDirAreAppliedToHtmlTag(): void
    {
        $html = $this->renderer->render($this->mjml('<mjml lang="nl" dir="ltr">'))->html;

        $this->assertStringContainsString('<html lang="nl" dir="ltr"', $html);
    }

    public function testRootLangAndDirAreAppliedToBodyWrapper(): void
    {
        $html = $this->renderer->render($this->mjml('<mjml lang="nl" dir="ltr">'))->html;

        $this->assertMatchesRegularExpression('/<div[^>]*role="article"[^>]*lang="nl"[^>]*dir="ltr"/', $html);
        $this->assertStringNotContainsString('lang="und"', $html);
        $this->assertStringNotContainsString('dir="auto"', $html);
    }

    private function mjml(string $rootTag): string
    {
        return $rootTag.'<mj-body><mj-section><mj-column><mj-text>Hoi</mj-text></mj-column></mj-section></mj-body></mjml>';
    }
}
