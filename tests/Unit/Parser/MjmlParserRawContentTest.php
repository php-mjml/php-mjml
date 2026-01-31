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

namespace PhpMjml\Tests\Unit\Parser;

use PhpMjml\Parser\MjmlParser;
use PHPUnit\Framework\TestCase;

/**
 * Tests for mj-raw content handling with various HTML edge cases.
 */
final class MjmlParserRawContentTest extends TestCase
{
    private MjmlParser $parser;

    protected function setUp(): void
    {
        $this->parser = new MjmlParser();
    }

    public function testParseRawWithValidSelfClosingTags(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><br/><hr/><img src="test.jpg"/></mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString('<br/>', $rawNode->content);
        $this->assertStringContainsString('<hr/>', $rawNode->content);
        $this->assertStringContainsString('<img', $rawNode->content);
    }

    public function testParseRawWithHtmlVoidTagBr(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><br></mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertSame('<br>', $rawNode->content);
    }

    public function testParseRawWithUnclosedDiv(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><div>unclosed</mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertSame('<div>unclosed', $rawNode->content);
    }

    public function testParseRawWithMismatchedTags(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><div>Content</span></mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertSame('<div>Content</span>', $rawNode->content);
    }

    public function testParseRawWithUnescapedAmpersandIsHandled(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><a href="https://example.com?foo=1&bar=2">Link</a></mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        // Parser should handle bare ampersands by escaping them
        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString('foo=1', $rawNode->content);
        $this->assertStringContainsString('bar=2', $rawNode->content);
    }

    public function testParseRawWithProperlyEscapedAmpersand(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><a href="https://example.com?foo=1&amp;bar=2">Link</a></mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString('&amp;', $rawNode->content);
    }

    public function testParseRawWithHtmlNamedEntitiesArePreserved(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><p>Copyright &copy; 2024 &mdash; All rights</p></mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        // Raw content is preserved exactly as written (no entity decoding)
        $this->assertStringContainsString('&copy;', $rawNode->content);
        $this->assertStringContainsString('&mdash;', $rawNode->content);
    }

    public function testParseRawWithNumericEntitiesArePreserved(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><p>&#169; &#8212; &#x00A9;</p></mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        // Raw content is preserved exactly as written (no entity decoding)
        $this->assertStringContainsString('&#169;', $rawNode->content);
        $this->assertStringContainsString('&#8212;', $rawNode->content);
    }

    public function testParseRawWithConditionalComments(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><!--[if mso]><table><tr><td><![endif]--></mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString('[if mso]', $rawNode->content);
    }

    public function testParseRawWithNestedValidHtml(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw>
                      <div class="wrapper">
                        <table role="presentation">
                          <tr>
                            <td>Cell</td>
                          </tr>
                        </table>
                      </div>
                    </mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString('<div class="wrapper">', $rawNode->content);
        $this->assertStringContainsString('<table', $rawNode->content);
        $this->assertStringContainsString('</table>', $rawNode->content);
        $this->assertStringContainsString('</div>', $rawNode->content);
    }

    public function testParseRawWithUnescapedLessThan(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw>5 < 10 is true</mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertSame('5 < 10 is true', $rawNode->content);
    }

    public function testParseRawWithEscapedLessThan(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw>5 &lt; 10 is true</mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString('&lt;', $rawNode->content);
    }

    public function testParseRawWithHtmlVoidTagHr(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><hr></mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertSame('<hr>', $rawNode->content);
    }

    public function testParseRawWithHtmlVoidTagImg(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><img src="test.jpg"></mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertSame('<img src="test.jpg">', $rawNode->content);
    }

    public function testParseRawWithNestedUnclosedTags(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><p><span>nested</mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertSame('<p><span>nested', $rawNode->content);
    }

    public function testParseRawWithMultipleRawBlocks(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><!--[if mso]><table><![endif]--></mj-raw>
                    <mj-text>Content</mj-text>
                    <mj-raw><!--[if mso]></table><![endif]--></mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $column = $node->children[0]->children[0]->children[0];
        $this->assertCount(3, $column->children);

        $rawNode1 = $column->children[0];
        $this->assertSame('mj-raw', $rawNode1->tagName);
        $this->assertStringContainsString('[if mso]><table>', $rawNode1->content);

        $textNode = $column->children[1];
        $this->assertSame('mj-text', $textNode->tagName);

        $rawNode2 = $column->children[2];
        $this->assertSame('mj-raw', $rawNode2->tagName);
        $this->assertStringContainsString('[if mso]></table>', $rawNode2->content);
    }

    public function testParseRawPreservesOriginalContentWithUnescapedAmpersand(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><a href="https://example.com?a=1&b=2">Link</a></mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $node = $this->parser->parse($mjml);

        $rawNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-raw', $rawNode->tagName);
        // The raw content should preserve the original &
        $this->assertSame('<a href="https://example.com?a=1&b=2">Link</a>', $rawNode->content);
    }
}
