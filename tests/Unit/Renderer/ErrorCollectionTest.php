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

final class ErrorCollectionTest extends TestCase
{
    private Mjml2Html $renderer;

    protected function setUp(): void
    {
        $registry = new Registry();
        $registry->registerMany(CorePreset::getComponents());

        $this->renderer = new Mjml2Html($registry, new MjmlParser());
    }

    public function testRenderReturnsEmptyErrorsForValidMjml(): void
    {
        $mjml = <<<'MJML'
<mjml>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text>Hello World</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        $result = $this->renderer->render($mjml);

        $this->assertSame([], $result->errors);
    }

    public function testRenderCollectsErrorForInvalidCssSelector(): void
    {
        $mjml = <<<'MJML'
<mjml>
  <mj-head>
    <mj-html-attributes>
      <mj-selector path="[invalid selector">
        <mj-html-attribute name="data-id">42</mj-html-attribute>
      </mj-selector>
    </mj-html-attributes>
  </mj-head>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text>Hello World</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        $result = $this->renderer->render($mjml);

        $this->assertNotEmpty($result->errors);
        $this->assertCount(1, $result->errors);
        $this->assertStringContainsString('mj-html-attributes', $result->errors[0]);
        $this->assertStringContainsString('[invalid selector', $result->errors[0]);
    }

    public function testRenderCollectsMultipleErrorsForMultipleInvalidSelectors(): void
    {
        $mjml = <<<'MJML'
<mjml>
  <mj-head>
    <mj-html-attributes>
      <mj-selector path="[invalid">
        <mj-html-attribute name="data-id">1</mj-html-attribute>
      </mj-selector>
      <mj-selector path="another[broken">
        <mj-html-attribute name="data-id">2</mj-html-attribute>
      </mj-selector>
    </mj-html-attributes>
  </mj-head>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text>Hello World</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        $result = $this->renderer->render($mjml);

        $this->assertCount(2, $result->errors);
    }

    public function testRenderStillProducesHtmlDespiteErrors(): void
    {
        $mjml = <<<'MJML'
<mjml>
  <mj-head>
    <mj-html-attributes>
      <mj-selector path="[invalid selector">
        <mj-html-attribute name="data-id">42</mj-html-attribute>
      </mj-selector>
    </mj-html-attributes>
  </mj-head>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text>Hello World</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        $result = $this->renderer->render($mjml);

        // Errors should be collected
        $this->assertNotEmpty($result->errors);

        // HTML should still be produced (graceful degradation)
        $this->assertStringContainsString('<!doctype html>', $result->html);
        $this->assertStringContainsString('Hello World', $result->html);
    }

    public function testValidSelectorWithInvalidSelectorDoesNotAffectValidOne(): void
    {
        $mjml = <<<'MJML'
<mjml>
  <mj-head>
    <mj-html-attributes>
      <mj-selector path=".valid-class">
        <mj-html-attribute name="data-valid">yes</mj-html-attribute>
      </mj-selector>
      <mj-selector path="[invalid">
        <mj-html-attribute name="data-invalid">no</mj-html-attribute>
      </mj-selector>
    </mj-html-attributes>
  </mj-head>
  <mj-body>
    <mj-section css-class="valid-class">
      <mj-column>
        <mj-text>Hello World</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        $result = $this->renderer->render($mjml);

        // One error for the invalid selector
        $this->assertCount(1, $result->errors);
        $this->assertStringContainsString('[invalid', $result->errors[0]);

        // The valid selector should still have been applied
        $this->assertStringContainsString('data-valid="yes"', $result->html);
    }

    public function testErrorsAreClearedBetweenRenderRuns(): void
    {
        $mjmlWithError = <<<'MJML'
<mjml>
  <mj-head>
    <mj-html-attributes>
      <mj-selector path="[invalid">
        <mj-html-attribute name="data-id">42</mj-html-attribute>
      </mj-selector>
    </mj-html-attributes>
  </mj-head>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text>First render</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        $mjmlValid = <<<'MJML'
<mjml>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text>Second render</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        // First render with error
        $firstResult = $this->renderer->render($mjmlWithError);
        $this->assertCount(1, $firstResult->errors);

        // Second render without error - errors should be cleared
        $secondResult = $this->renderer->render($mjmlValid);
        $this->assertSame([], $secondResult->errors);
        $this->assertStringContainsString('Second render', $secondResult->html);
    }
}
