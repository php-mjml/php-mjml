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
use PhpMjml\Parser\ParserException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for invalid XHTML handling in MJML ending tags.
 *
 * HTML void tags like <br>, <hr>, <img> are valid in HTML but invalid in XML/XHTML
 * unless self-closed (<br/>, <hr/>, <img/>). This test documents which MJML tags
 * can handle invalid XHTML.
 *
 * Summary of behavior:
 * - All ending tags allow invalid XHTML (void tags, unclosed tags) - content is extracted before XML parsing
 *
 * @see https://documentation.mjml.io/#ending-tags
 */
final class InvalidXhtmlTest extends TestCase
{
    /**
     * Overview of all ending tags and their invalid XHTML handling.
     *
     * @var array<string, array{allowsInvalidXhtml: bool, reason: string}>
     */
    public const ENDING_TAGS_OVERVIEW = [
        'mj-text' => [
            'allowsInvalidXhtml' => true,
            'reason' => 'Content extracted with placeholders before XML parsing',
        ],
        'mj-button' => [
            'allowsInvalidXhtml' => true,
            'reason' => 'Content extracted with placeholders before XML parsing',
        ],
        'mj-table' => [
            'allowsInvalidXhtml' => true,
            'reason' => 'Content extracted with placeholders before XML parsing',
        ],
        'mj-navbar-link' => [
            'allowsInvalidXhtml' => true,
            'reason' => 'Content extracted with placeholders before XML parsing',
        ],
        'mj-accordion-title' => [
            'allowsInvalidXhtml' => true,
            'reason' => 'Content extracted with placeholders before XML parsing',
        ],
        'mj-accordion-text' => [
            'allowsInvalidXhtml' => true,
            'reason' => 'Content extracted with placeholders before XML parsing',
        ],
        'mj-social-element' => [
            'allowsInvalidXhtml' => true,
            'reason' => 'Content extracted with placeholders before XML parsing',
        ],
        'mj-raw' => [
            'allowsInvalidXhtml' => true,
            'reason' => 'Content extracted with placeholders before XML parsing',
        ],
    ];
    private MjmlParser $parser;

    protected function setUp(): void
    {
        $this->parser = new MjmlParser();
    }

    // =========================================================================
    // mj-raw: ALLOWS invalid XHTML
    // =========================================================================

    public function testMjRawAllowsVoidTagBr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw>Line 1<br>Line 2</mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $rawNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString('<br>', $rawNode->content);
    }

    public function testMjRawAllowsVoidTagHr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw><hr></mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $rawNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString('<hr>', $rawNode->content);
    }

    public function testMjRawAllowsVoidTagImg(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw><img src="test.jpg"></mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $rawNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString('<img src="test.jpg">', $rawNode->content);
    }

    public function testMjRawAllowsMultipleVoidTags(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw><br><hr><br><input type="text"><br></mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $rawNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString('<br>', $rawNode->content);
        $this->assertStringContainsString('<hr>', $rawNode->content);
        $this->assertStringContainsString('<input type="text">', $rawNode->content);
    }

    // =========================================================================
    // mj-text: ALLOWS invalid XHTML
    // =========================================================================

    public function testMjTextAllowsVoidTagBr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text>Line 1<br>Line 2</mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertStringContainsString('<br>', $textNode->content);
    }

    public function testMjTextAllowsVoidTagHr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text><hr></mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertStringContainsString('<hr>', $textNode->content);
    }

    public function testMjTextAllowsVoidTagImg(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text><img src="test.jpg"></mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertStringContainsString('<img src="test.jpg">', $textNode->content);
    }

    public function testMjTextWorksWithSelfClosedBr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text>Line 1<br/>Line 2</mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertStringContainsString('<br/>', $textNode->content);
    }

    // =========================================================================
    // mj-button: ALLOWS invalid XHTML
    // =========================================================================

    public function testMjButtonAllowsVoidTagBr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-button href="#">Click<br>Me</mj-button>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $buttonNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-button', $buttonNode->tagName);
        $this->assertStringContainsString('<br>', $buttonNode->content);
    }

    public function testMjButtonAllowsVoidTagImg(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-button href="#"><img src="icon.png"> Download</mj-button>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $buttonNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-button', $buttonNode->tagName);
        $this->assertStringContainsString('<img src="icon.png">', $buttonNode->content);
    }

    public function testMjButtonWorksWithSelfClosedImg(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-button href="#"><img src="icon.png"/> Download</mj-button>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $buttonNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-button', $buttonNode->tagName);
        $this->assertStringContainsString('<img', $buttonNode->content);
    }

    // =========================================================================
    // mj-table: ALLOWS invalid XHTML
    // =========================================================================

    public function testMjTableAllowsVoidTagBr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-table>
                <tr><td>Cell with<br>line break</td></tr>
            </mj-table>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $tableNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-table', $tableNode->tagName);
        $this->assertStringContainsString('<br>', $tableNode->content);
    }

    public function testMjTableWorksWithSelfClosedBr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-table>
                <tr><td>Cell with<br/>line break</td></tr>
            </mj-table>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $tableNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-table', $tableNode->tagName);
        $this->assertStringContainsString('<br/>', $tableNode->content);
    }

    // =========================================================================
    // mj-navbar-link: ALLOWS invalid XHTML
    // =========================================================================

    public function testMjNavbarLinkAllowsVoidTagImg(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-navbar>
                <mj-navbar-link href="#"><img src="icon.png"> Home</mj-navbar-link>
            </mj-navbar>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $navbar = $node->children[0]->children[0]->children[0]->children[0];
        $linkNode = $navbar->children[0];

        $this->assertSame('mj-navbar-link', $linkNode->tagName);
        $this->assertStringContainsString('<img src="icon.png">', $linkNode->content);
    }

    public function testMjNavbarLinkWorksWithSelfClosedImg(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-navbar>
                <mj-navbar-link href="#"><img src="icon.png"/> Home</mj-navbar-link>
            </mj-navbar>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $navbar = $node->children[0]->children[0]->children[0]->children[0];
        $linkNode = $navbar->children[0];

        $this->assertSame('mj-navbar-link', $linkNode->tagName);
        $this->assertStringContainsString('<img', $linkNode->content);
    }

    // =========================================================================
    // mj-accordion-title: ALLOWS invalid XHTML
    // =========================================================================

    public function testMjAccordionTitleAllowsVoidTagBr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-accordion>
                <mj-accordion-element>
                    <mj-accordion-title>Question<br>Line 2</mj-accordion-title>
                    <mj-accordion-text>Answer</mj-accordion-text>
                </mj-accordion-element>
            </mj-accordion>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $accordion = $node->children[0]->children[0]->children[0]->children[0];
        $element = $accordion->children[0];
        $titleNode = $element->children[0];

        $this->assertSame('mj-accordion-title', $titleNode->tagName);
        $this->assertStringContainsString('<br>', $titleNode->content);
    }

    public function testMjAccordionTitleWorksWithSelfClosedBr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-accordion>
                <mj-accordion-element>
                    <mj-accordion-title>Question<br/>Line 2</mj-accordion-title>
                    <mj-accordion-text>Answer</mj-accordion-text>
                </mj-accordion-element>
            </mj-accordion>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $accordion = $node->children[0]->children[0]->children[0]->children[0];
        $element = $accordion->children[0];
        $titleNode = $element->children[0];

        $this->assertSame('mj-accordion-title', $titleNode->tagName);
        $this->assertStringContainsString('<br/>', $titleNode->content);
    }

    // =========================================================================
    // mj-accordion-text: ALLOWS invalid XHTML
    // =========================================================================

    public function testMjAccordionTextAllowsVoidTagBr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-accordion>
                <mj-accordion-element>
                    <mj-accordion-title>Question</mj-accordion-title>
                    <mj-accordion-text>Answer line 1<br>Answer line 2</mj-accordion-text>
                </mj-accordion-element>
            </mj-accordion>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $accordion = $node->children[0]->children[0]->children[0]->children[0];
        $element = $accordion->children[0];
        $textNode = $element->children[1];

        $this->assertSame('mj-accordion-text', $textNode->tagName);
        $this->assertStringContainsString('<br>', $textNode->content);
    }

    public function testMjAccordionTextWorksWithSelfClosedBr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-accordion>
                <mj-accordion-element>
                    <mj-accordion-title>Question</mj-accordion-title>
                    <mj-accordion-text>Answer line 1<br/>Answer line 2</mj-accordion-text>
                </mj-accordion-element>
            </mj-accordion>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $accordion = $node->children[0]->children[0]->children[0]->children[0];
        $element = $accordion->children[0];
        $textNode = $element->children[1];

        $this->assertSame('mj-accordion-text', $textNode->tagName);
        $this->assertStringContainsString('<br/>', $textNode->content);
    }

    // =========================================================================
    // mj-social-element: ALLOWS invalid XHTML
    // =========================================================================

    public function testMjSocialElementAllowsVoidTagBr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-social>
                <mj-social-element name="facebook" href="#">Share<br>on Facebook</mj-social-element>
            </mj-social>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $social = $node->children[0]->children[0]->children[0]->children[0];
        $elementNode = $social->children[0];

        $this->assertSame('mj-social-element', $elementNode->tagName);
        $this->assertStringContainsString('<br>', $elementNode->content);
    }

    public function testMjSocialElementWorksWithSelfClosedBr(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-social>
                <mj-social-element name="facebook" href="#">Share<br/>on Facebook</mj-social-element>
            </mj-social>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $social = $node->children[0]->children[0]->children[0]->children[0];
        $elementNode = $social->children[0];

        $this->assertSame('mj-social-element', $elementNode->tagName);
        $this->assertStringContainsString('<br/>', $elementNode->content);
    }

    // =========================================================================
    // Comprehensive void tag tests
    // =========================================================================

    /**
     * Tests all HTML void tags in mj-text (representative of all ending tags).
     *
     * @dataProvider htmlVoidTagsProvider
     */
    public function testMjTextAllowsAllHtmlVoidTags(string $voidTag): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text>Content with '.$voidTag.' in it</mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertStringContainsString($voidTag, $textNode->content);
    }

    /**
     * Tests all HTML void tags in mj-raw (should all succeed).
     *
     * @dataProvider htmlVoidTagsProvider
     */
    public function testMjRawAllowsAllHtmlVoidTags(string $voidTag): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw>Content with '.$voidTag.' in it</mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $rawNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString($voidTag, $rawNode->content);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function htmlVoidTagsProvider(): array
    {
        return [
            'br' => ['<br>'],
            'hr' => ['<hr>'],
            'img' => ['<img src="test.jpg">'],
            'input' => ['<input type="text">'],
            'meta' => ['<meta name="test" content="value">'],
            'link' => ['<link rel="stylesheet" href="style.css">'],
            'area' => ['<area shape="rect" coords="0,0,0,0">'],
            'base' => ['<base href="/">'],
            'col' => ['<col span="1">'],
            'embed' => ['<embed src="movie.swf">'],
            'source' => ['<source src="audio.mp3">'],
            'track' => ['<track src="subtitles.vtt">'],
            'wbr' => ['<wbr>'],
        ];
    }

    // =========================================================================
    // Summary test: documents the overview
    // =========================================================================

    public function testEndingTagsOverviewIsAccurate(): void
    {
        // Verify that mj-raw allows invalid XHTML
        $mjmlRaw = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw><br><hr><img src="t.jpg"></mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjmlRaw);
        $this->assertSame('mj-raw', $node->children[0]->children[0]->children[0]->children[0]->tagName);

        // Verify that mj-text also allows invalid XHTML
        $mjmlText = '<mjml><mj-body><mj-section><mj-column>
            <mj-text><br></mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjmlText);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];
        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertStringContainsString('<br>', $textNode->content);

        // Confirm the constant reflects actual behavior - all ending tags allow invalid XHTML
        $this->assertTrue(self::ENDING_TAGS_OVERVIEW['mj-raw']['allowsInvalidXhtml']);
        $this->assertTrue(self::ENDING_TAGS_OVERVIEW['mj-text']['allowsInvalidXhtml']);
        $this->assertTrue(self::ENDING_TAGS_OVERVIEW['mj-button']['allowsInvalidXhtml']);
        $this->assertTrue(self::ENDING_TAGS_OVERVIEW['mj-table']['allowsInvalidXhtml']);
        $this->assertTrue(self::ENDING_TAGS_OVERVIEW['mj-navbar-link']['allowsInvalidXhtml']);
        $this->assertTrue(self::ENDING_TAGS_OVERVIEW['mj-accordion-title']['allowsInvalidXhtml']);
        $this->assertTrue(self::ENDING_TAGS_OVERVIEW['mj-accordion-text']['allowsInvalidXhtml']);
        $this->assertTrue(self::ENDING_TAGS_OVERVIEW['mj-social-element']['allowsInvalidXhtml']);
    }

    // =========================================================================
    // Nested ending tags limitation
    // =========================================================================

    public function testNestedEndingTagsAreNotSupported(): void
    {
        // Nesting the same ending tag inside itself is not supported
        // This matches official MJML spec: "mj-raw cannot contain other MJML components"
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw><mj-raw>nested</mj-raw></mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $this->expectException(ParserException::class);
        $this->parser->parse($mjml);
    }

    public function testSelfClosingEndingTagsAreIgnored(): void
    {
        // Self-closing ending tags (like in mj-attributes) should not be processed
        // as ending tags - they have no content to extract
        $mjml = '<mjml>
            <mj-head>
                <mj-attributes>
                    <mj-text color="blue" />
                </mj-attributes>
            </mj-head>
            <mj-body><mj-section><mj-column>
                <mj-text><br>Line break here</mj-text>
            </mj-column></mj-section></mj-body>
        </mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[1]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertStringContainsString('<br>', $textNode->content);
    }
}
