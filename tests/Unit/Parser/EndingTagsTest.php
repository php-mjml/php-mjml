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
 * Tests for MJML "Ending Tags" - components that contain text/HTML content.
 *
 * Ending tags can contain both text and HTML content, which remains unprocessed
 * by the MJML engine. They cannot contain other MJML components.
 *
 * @see https://documentation.mjml.io/#ending-tags
 */
final class EndingTagsTest extends TestCase
{
    private MjmlParser $parser;

    protected function setUp(): void
    {
        $this->parser = new MjmlParser();
    }

    // =========================================================================
    // mj-text tests
    // =========================================================================

    public function testMjTextWithPlainText(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text>Hello World</mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertSame('Hello World', $textNode->content);
    }

    public function testMjTextWithHtmlTags(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text><p>Paragraph with <strong>bold</strong> and <em>italic</em></p></mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertStringContainsString('<p>', $textNode->content);
        $this->assertStringContainsString('<strong>bold</strong>', $textNode->content);
        $this->assertStringContainsString('<em>italic</em>', $textNode->content);
    }

    public function testMjTextWithNestedHtml(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text>
                <div class="wrapper">
                    <ul>
                        <li>Item 1</li>
                        <li>Item 2</li>
                    </ul>
                </div>
            </mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertStringContainsString('<div class="wrapper">', $textNode->content);
        $this->assertStringContainsString('<ul>', $textNode->content);
        $this->assertStringContainsString('<li>Item 1</li>', $textNode->content);
    }

    public function testMjTextWithEscapedEntities(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text>Use &lt;div&gt; for containers and &amp; for ampersands</mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        // Entities are preserved as-is in innerHTML (escaped form)
        $this->assertStringContainsString('&lt;div&gt;', $textNode->content);
        $this->assertStringContainsString('&amp;', $textNode->content);
    }

    public function testMjTextWithHtmlEntities(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text>Price: &euro;99 &mdash; Sale!</mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        // HTML entities converted to numeric then decoded
        $this->assertStringContainsString('99', $textNode->content);
    }

    public function testMjTextWithLinks(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text>
                <a href="https://example.com?utm_source=email&amp;utm_medium=link">Click here</a>
            </mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertStringContainsString('<a href=', $textNode->content);
        $this->assertStringContainsString('utm_source=email', $textNode->content);
    }

    // =========================================================================
    // mj-button tests
    // =========================================================================

    public function testMjButtonWithPlainText(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-button href="https://example.com">Click Me</mj-button>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $buttonNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-button', $buttonNode->tagName);
        $this->assertSame('Click Me', $buttonNode->content);
        $this->assertSame('https://example.com', $buttonNode->attributes['href']);
    }

    public function testMjButtonWithHtmlContent(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-button href="#">
                <span style="font-weight: bold;">Subscribe</span> Now!
            </mj-button>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $buttonNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-button', $buttonNode->tagName);
        $this->assertStringContainsString('<span', $buttonNode->content);
        $this->assertStringContainsString('font-weight: bold', $buttonNode->content);
    }

    public function testMjButtonWithIcon(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-button href="#">
                <img src="icon.png" alt="" style="vertical-align: middle;"/> Download
            </mj-button>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $buttonNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-button', $buttonNode->tagName);
        $this->assertStringContainsString('<img', $buttonNode->content);
        $this->assertStringContainsString('Download', $buttonNode->content);
    }

    // =========================================================================
    // mj-table tests
    // =========================================================================

    public function testMjTableWithBasicContent(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-table>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                </tr>
                <tr>
                    <td>Product A</td>
                    <td>$10</td>
                </tr>
            </mj-table>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $tableNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-table', $tableNode->tagName);
        $this->assertStringContainsString('<tr>', $tableNode->content);
        $this->assertStringContainsString('<th>Name</th>', $tableNode->content);
        $this->assertStringContainsString('<td>Product A</td>', $tableNode->content);
    }

    public function testMjTableWithStyledCells(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-table>
                <tr>
                    <td style="padding: 10px; background-color: #f0f0f0;">Styled cell</td>
                </tr>
            </mj-table>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $tableNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-table', $tableNode->tagName);
        $this->assertStringContainsString('padding: 10px', $tableNode->content);
        $this->assertStringContainsString('background-color: #f0f0f0', $tableNode->content);
    }

    // =========================================================================
    // mj-raw tests (lenient HTML parsing)
    // =========================================================================

    public function testMjRawWithHtmlVoidTags(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw><br><hr><img src="test.jpg"></mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $rawNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString('<br>', $rawNode->content);
        $this->assertStringContainsString('<hr>', $rawNode->content);
        $this->assertStringContainsString('<img src="test.jpg">', $rawNode->content);
    }

    public function testMjRawWithUnclosedTags(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw><div><p>Unclosed tags</mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $rawNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertSame('<div><p>Unclosed tags', $rawNode->content);
    }

    public function testMjRawWithUnescapedCharacters(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw>if (x < 10 && y > 5) { return true; }</mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $rawNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertSame('if (x < 10 && y > 5) { return true; }', $rawNode->content);
    }

    public function testMjRawWithMsoConditionalComments(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw>
                <!--[if mso]>
                <table role="presentation" cellpadding="0" cellspacing="0">
                    <tr><td>
                <![endif]-->
            </mj-raw>
            <mj-text>Content</mj-text>
            <mj-raw>
                <!--[if mso]>
                    </td></tr>
                </table>
                <![endif]-->
            </mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $column = $node->children[0]->children[0]->children[0];

        $this->assertCount(3, $column->children);

        $rawNode1 = $column->children[0];
        $this->assertSame('mj-raw', $rawNode1->tagName);
        $this->assertStringContainsString('[if mso]', $rawNode1->content);

        $rawNode2 = $column->children[2];
        $this->assertSame('mj-raw', $rawNode2->tagName);
        $this->assertStringContainsString('[if mso]', $rawNode2->content);
    }

    public function testMjRawWithTemplatingLanguageSyntax(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw>{% if user.premium %}<span class="badge">Premium</span>{% endif %}</mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $rawNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString('{% if user.premium %}', $rawNode->content);
        $this->assertStringContainsString('{% endif %}', $rawNode->content);
    }

    public function testMjRawWithHandlebarsTemplating(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw>{{#if showBanner}}<div class="banner">{{bannerText}}</div>{{/if}}</mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $rawNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-raw', $rawNode->tagName);
        $this->assertStringContainsString('{{#if showBanner}}', $rawNode->content);
        $this->assertStringContainsString('{{bannerText}}', $rawNode->content);
    }

    // =========================================================================
    // mj-navbar-link tests
    // =========================================================================

    public function testMjNavbarLinkWithPlainText(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-navbar>
                <mj-navbar-link href="#">Home</mj-navbar-link>
            </mj-navbar>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $navbar = $node->children[0]->children[0]->children[0]->children[0];
        $linkNode = $navbar->children[0];

        $this->assertSame('mj-navbar-link', $linkNode->tagName);
        $this->assertSame('Home', $linkNode->content);
    }

    public function testMjNavbarLinkWithIconAndText(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-navbar>
                <mj-navbar-link href="#">
                    <img src="icon.png" alt="" style="width: 16px;"/> Home
                </mj-navbar-link>
            </mj-navbar>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $navbar = $node->children[0]->children[0]->children[0]->children[0];
        $linkNode = $navbar->children[0];

        $this->assertSame('mj-navbar-link', $linkNode->tagName);
        $this->assertStringContainsString('<img', $linkNode->content);
        $this->assertStringContainsString('Home', $linkNode->content);
    }

    // =========================================================================
    // mj-accordion-title tests
    // =========================================================================

    public function testMjAccordionTitleWithPlainText(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-accordion>
                <mj-accordion-element>
                    <mj-accordion-title>FAQ Question 1</mj-accordion-title>
                    <mj-accordion-text>Answer 1</mj-accordion-text>
                </mj-accordion-element>
            </mj-accordion>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $accordion = $node->children[0]->children[0]->children[0]->children[0];
        $element = $accordion->children[0];
        $titleNode = $element->children[0];

        $this->assertSame('mj-accordion-title', $titleNode->tagName);
        $this->assertSame('FAQ Question 1', $titleNode->content);
    }

    public function testMjAccordionTitleWithHtml(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-accordion>
                <mj-accordion-element>
                    <mj-accordion-title><strong>Important:</strong> How do I reset my password?</mj-accordion-title>
                    <mj-accordion-text>Instructions here</mj-accordion-text>
                </mj-accordion-element>
            </mj-accordion>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $accordion = $node->children[0]->children[0]->children[0]->children[0];
        $element = $accordion->children[0];
        $titleNode = $element->children[0];

        $this->assertSame('mj-accordion-title', $titleNode->tagName);
        $this->assertStringContainsString('<strong>Important:</strong>', $titleNode->content);
    }

    // =========================================================================
    // mj-accordion-text tests
    // =========================================================================

    public function testMjAccordionTextWithPlainText(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-accordion>
                <mj-accordion-element>
                    <mj-accordion-title>Question</mj-accordion-title>
                    <mj-accordion-text>This is the answer to the question.</mj-accordion-text>
                </mj-accordion-element>
            </mj-accordion>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $accordion = $node->children[0]->children[0]->children[0]->children[0];
        $element = $accordion->children[0];
        $textNode = $element->children[1];

        $this->assertSame('mj-accordion-text', $textNode->tagName);
        $this->assertSame('This is the answer to the question.', $textNode->content);
    }

    public function testMjAccordionTextWithRichHtml(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-accordion>
                <mj-accordion-element>
                    <mj-accordion-title>Question</mj-accordion-title>
                    <mj-accordion-text>
                        <p>Here are the steps:</p>
                        <ol>
                            <li>Step one</li>
                            <li>Step two</li>
                        </ol>
                        <p>For more info, <a href="#">click here</a>.</p>
                    </mj-accordion-text>
                </mj-accordion-element>
            </mj-accordion>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $accordion = $node->children[0]->children[0]->children[0]->children[0];
        $element = $accordion->children[0];
        $textNode = $element->children[1];

        $this->assertSame('mj-accordion-text', $textNode->tagName);
        $this->assertStringContainsString('<ol>', $textNode->content);
        $this->assertStringContainsString('<li>Step one</li>', $textNode->content);
        $this->assertStringContainsString('<a href="#">click here</a>', $textNode->content);
    }

    // =========================================================================
    // mj-social-element tests
    // =========================================================================

    public function testMjSocialElementWithPlainText(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-social>
                <mj-social-element name="facebook" href="#">Share</mj-social-element>
            </mj-social>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $social = $node->children[0]->children[0]->children[0]->children[0];
        $elementNode = $social->children[0];

        $this->assertSame('mj-social-element', $elementNode->tagName);
        $this->assertSame('Share', $elementNode->content);
        $this->assertSame('facebook', $elementNode->attributes['name']);
    }

    public function testMjSocialElementWithHtmlContent(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-social>
                <mj-social-element name="twitter" href="#">
                    <span style="text-transform: uppercase;">Tweet</span>
                </mj-social-element>
            </mj-social>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $social = $node->children[0]->children[0]->children[0]->children[0];
        $elementNode = $social->children[0];

        $this->assertSame('mj-social-element', $elementNode->tagName);
        $this->assertStringContainsString('<span', $elementNode->content);
        $this->assertStringContainsString('text-transform: uppercase', $elementNode->content);
    }

    // =========================================================================
    // Edge cases
    // =========================================================================

    public function testEndingTagPreservesWhitespace(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text>
                Line 1
                Line 2
                Line 3
            </mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertStringContainsString('Line 1', $textNode->content);
        $this->assertStringContainsString('Line 2', $textNode->content);
        $this->assertStringContainsString('Line 3', $textNode->content);
    }

    public function testEndingTagWithEmptyContent(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text></mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertSame('', $textNode->content);
    }

    public function testEndingTagWithOnlyWhitespace(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text>   </mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        // Whitespace is preserved in ending tag content
        $this->assertSame('   ', $textNode->content);
    }

    public function testEndingTagWithSpecialXmlCharactersEscaped(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text>Compare: &lt;div&gt; vs &lt;span&gt; elements</mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        // Entities are preserved in innerHTML
        $this->assertStringContainsString('&lt;div&gt;', $textNode->content);
        $this->assertStringContainsString('&lt;span&gt;', $textNode->content);
    }

    public function testEndingTagDoesNotParseNestedMjmlTags(): void
    {
        // Even if someone accidentally puts MJML tags inside an ending tag,
        // they should be treated as raw HTML, not parsed as MJML
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text><mj-button>This is not a real button</mj-button></mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        // The mj-button should be in the content as raw HTML, not parsed as a child
        $this->assertStringContainsString('<mj-button>', $textNode->content);
        $this->assertEmpty($textNode->children);
    }

    public function testMultipleEndingTagsInSameColumn(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text>First text block</mj-text>
            <mj-button href="#">Click Me</mj-button>
            <mj-text>Second text block</mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $column = $node->children[0]->children[0]->children[0];

        $this->assertCount(3, $column->children);

        $this->assertSame('mj-text', $column->children[0]->tagName);
        $this->assertSame('First text block', $column->children[0]->content);

        $this->assertSame('mj-button', $column->children[1]->tagName);
        $this->assertSame('Click Me', $column->children[1]->content);

        $this->assertSame('mj-text', $column->children[2]->tagName);
        $this->assertSame('Second text block', $column->children[2]->content);
    }

    public function testAllEndingTagsAllowInvalidXhtml(): void
    {
        // All ending tags now have lenient HTML parsing (void tags allowed)
        $mjmlRaw = '<mjml><mj-body><mj-section><mj-column>
            <mj-raw><br><hr></mj-raw>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjmlRaw);
        $rawNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-raw', $rawNode->tagName);
        // mj-raw preserves void tags without self-closing
        $this->assertSame('<br><hr>', $rawNode->content);
    }

    // =========================================================================
    // Void tags in ending tags (lenient HTML parsing)
    // =========================================================================

    public function testMjTextWithHtmlVoidTags(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-text>Line 1<br>Line 2<hr>Section break</mj-text>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $textNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-text', $textNode->tagName);
        $this->assertStringContainsString('<br>', $textNode->content);
        $this->assertStringContainsString('<hr>', $textNode->content);
    }

    public function testMjButtonWithVoidTagImg(): void
    {
        $mjml = '<mjml><mj-body><mj-section><mj-column>
            <mj-button href="#"><img src="icon.png"> Download</mj-button>
        </mj-column></mj-section></mj-body></mjml>';

        $node = $this->parser->parse($mjml);
        $buttonNode = $node->children[0]->children[0]->children[0]->children[0];

        $this->assertSame('mj-button', $buttonNode->tagName);
        $this->assertStringContainsString('<img src="icon.png">', $buttonNode->content);
        $this->assertStringContainsString('Download', $buttonNode->content);
    }

    public function testMjTableWithVoidTags(): void
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
}
