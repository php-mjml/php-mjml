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
use PhpMjml\Parser\MjmlParser;
use PhpMjml\Preset\CorePreset;
use PhpMjml\Renderer\Mjml2Html;
use PHPUnit\Framework\TestCase;

/**
 * Tests that mj-style inline="inline" CSS rules are inlined into
 * matching HTML elements' style attributes (like Juice does in JS MJML).
 */
final class InlineStyleTest extends TestCase
{
    private Mjml2Html $renderer;

    protected function setUp(): void
    {
        $registry = new Registry();
        $registry->registerMany(CorePreset::getComponents());

        $this->renderer = new Mjml2Html($registry, new MjmlParser(registry: $registry));
    }

    public function testInlineStyleIsAppliedToMatchingCssClass(): void
    {
        $mjml = <<<'MJML'
<mjml>
  <mj-head>
    <mj-style inline="inline">
      .card { border-radius: 18px; overflow: hidden; }
    </mj-style>
  </mj-head>
  <mj-body>
    <mj-section css-class="card" background-color="#ffffff">
      <mj-column>
        <mj-text>Hello</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        $result = $this->renderer->render($mjml);

        // The element with class="card" should have border-radius inlined
        // Use lookaheads to match regardless of attribute order (style may come before class)
        $this->assertMatchesRegularExpression(
            '/<[a-z]+\s(?=[^>]*\bclass="[^"]*\bcard\b)(?=[^>]*\bstyle="[^"]*border-radius:\s*18px)[^>]*>/i',
            $result->html,
            'Expected border-radius:18px to be inlined on the element with class "card"'
        );

        $this->assertMatchesRegularExpression(
            '/<[a-z]+\s(?=[^>]*\bclass="[^"]*\bcard\b)(?=[^>]*\bstyle="[^"]*overflow:\s*hidden)[^>]*>/i',
            $result->html,
            'Expected overflow:hidden to be inlined on the element with class "card"'
        );
    }

    public function testInlineStyleAppliesToMultipleMatchingElements(): void
    {
        $mjml = <<<'MJML'
<mjml>
  <mj-head>
    <mj-style inline="inline">
      .highlight { background-color: yellow; }
    </mj-style>
  </mj-head>
  <mj-body>
    <mj-section css-class="highlight">
      <mj-column>
        <mj-text>First</mj-text>
      </mj-column>
    </mj-section>
    <mj-section css-class="highlight">
      <mj-column>
        <mj-text>Second</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        $result = $this->renderer->render($mjml);

        // Both sections with class="highlight" should have the style inlined
        // Use lookaheads to match regardless of attribute order
        $matchCount = preg_match_all(
            '/<[a-z]+\s(?=[^>]*\bclass="[^"]*\bhighlight\b)(?=[^>]*\bstyle="[^"]*background-color:\s*yellow)[^>]*>/i',
            $result->html
        );

        $this->assertGreaterThanOrEqual(2, $matchCount, 'Expected background-color:yellow inlined on at least 2 elements with class "highlight"');
    }

    public function testNonInlineStyleIsNotInlinedButInHead(): void
    {
        $mjml = <<<'MJML'
<mjml>
  <mj-head>
    <mj-style>.card { border-radius: 18px; }</mj-style>
  </mj-head>
  <mj-body>
    <mj-section css-class="card">
      <mj-column>
        <mj-text>Hello</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        $result = $this->renderer->render($mjml);

        // Non-inline styles should appear in a <style> tag in the head
        $this->assertStringContainsString('.card { border-radius: 18px; }', $result->html);

        // The style block should be within a <style> tag
        $this->assertMatchesRegularExpression(
            '/<style[^>]*>.*\.card\s*\{[^}]*border-radius:\s*18px[^}]*\}.*<\/style>/s',
            $result->html,
            'Non-inline style should appear in a <style> tag in the head'
        );
    }
}
