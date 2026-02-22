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
 * Tests that mj-font declarations produce <link> and @import tags in the output.
 */
final class FontRenderTest extends TestCase
{
    private Mjml2Html $renderer;

    protected function setUp(): void
    {
        $registry = new Registry();
        $registry->registerMany(CorePreset::getComponents());

        $this->renderer = new Mjml2Html($registry, new MjmlParser(registry: $registry));
    }

    public function testFontWithQuotedFamilyNameIsLoaded(): void
    {
        $mjml = <<<'MJML'
<mjml>
  <mj-head>
    <mj-font name="Righteous" href="https://fonts.googleapis.com/css2?family=Righteous&display=swap" />
  </mj-head>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text font-family="'Righteous', cursive">Hello</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        $html = $this->renderer->render($mjml)->html;

        $this->assertStringContainsString(
            'fonts.googleapis.com/css2?family=Righteous',
            $html,
            'Font link tag should be present when font-family uses quoted name'
        );
        $this->assertStringContainsString(
            '@import url(https://fonts.googleapis.com/css2?family=Righteous',
            $html,
            'Font @import should be present when font-family uses quoted name'
        );
    }

    public function testFontWithUnquotedFamilyNameIsLoaded(): void
    {
        $mjml = <<<'MJML'
<mjml>
  <mj-head>
    <mj-font name="Raleway" href="https://fonts.googleapis.com/css?family=Raleway" />
  </mj-head>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text font-family="Raleway, Arial">Hello</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        $html = $this->renderer->render($mjml)->html;

        $this->assertStringContainsString(
            'fonts.googleapis.com/css?family=Raleway',
            $html,
            'Font link tag should be present when font-family uses unquoted name'
        );
    }

    public function testMultipleFontsWithQuotedNamesAreLoaded(): void
    {
        $mjml = <<<'MJML'
<mjml>
  <mj-head>
    <mj-font name="Righteous" href="https://fonts.googleapis.com/css2?family=Righteous&display=swap" />
    <mj-font name="Lato" href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" />
  </mj-head>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text font-family="'Righteous', cursive">Title</mj-text>
        <mj-text font-family="'Lato', Helvetica, sans-serif">Body</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        $html = $this->renderer->render($mjml)->html;

        $this->assertStringContainsString(
            'family=Righteous',
            $html,
            'Righteous font should be loaded'
        );
        $this->assertStringContainsString(
            'family=Lato',
            $html,
            'Lato font should be loaded'
        );
    }

    public function testUnusedFontIsNotLoaded(): void
    {
        $mjml = <<<'MJML'
<mjml>
  <mj-head>
    <mj-font name="Righteous" href="https://fonts.googleapis.com/css2?family=Righteous&display=swap" />
  </mj-head>
  <mj-body>
    <mj-section>
      <mj-column>
        <mj-text font-family="Arial, sans-serif">Hello</mj-text>
      </mj-column>
    </mj-section>
  </mj-body>
</mjml>
MJML;

        $html = $this->renderer->render($mjml)->html;

        $this->assertStringNotContainsString(
            'fonts.googleapis.com/css2?family=Righteous',
            $html,
            'Unused font should not be loaded'
        );
    }
}
