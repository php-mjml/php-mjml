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

use PhpMjml\Components\Body\Raw;
use PhpMjml\Parser\MjmlParser;
use PhpMjml\Parser\ParserException;
use PhpMjml\Renderer\Mjml2Html;
use PhpMjml\Renderer\RenderOptions;
use PhpMjml\Tests\Support\Html5Document;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ParserHardeningTest extends TestCase
{
    private MjmlParser $parser;

    protected function setUp(): void
    {
        $this->parser = new MjmlParser();
    }

    #[DataProvider('doctypeProvider')]
    public function testDocumentTypeDeclarationsAreRejected(string $mjml): void
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage('document type declarations');

        $this->parser->parse($mjml);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function doctypeProvider(): array
    {
        $bigEntity = str_repeat('x', 1000);

        return [
            'quadratic blowup' => ['<!DOCTYPE mjml [<!ENTITY q "'.$bigEntity.'">]><mjml lang="'.str_repeat('&q;', 100).'"><mj-body></mj-body></mjml>'],
            'external entity' => ['<!DOCTYPE mjml [<!ENTITY x SYSTEM "file:///etc/hosts">]><mjml><mj-head><mj-title>&x;</mj-title></mj-head></mjml>'],
            'external dtd' => ['<!DOCTYPE mjml SYSTEM "http://127.0.0.1/x.dtd"><mjml></mjml>'],
            'lowercase' => ['<!doctype mjml><mjml></mjml>'],
        ];
    }

    public function testDoctypeInsideEndingTagContentIsAllowed(): void
    {
        $node = $this->parser->parse('<mjml><mj-body><mj-raw><!DOCTYPE html></mj-raw></mj-body></mjml>');

        $this->assertSame('<!DOCTYPE html>', $node->children[0]->children[0]->content);
    }

    #[DataProvider('abruptCommentProvider')]
    public function testCommentsCannotCloseThemselvesEarly(string $comment): void
    {
        $html = Mjml2Html::create()->render(
            '<mjml><mj-body><mj-section><mj-column>'.$comment.'</mj-column></mj-section></mj-body></mjml>'
        )->html;

        $this->assertFalse(Html5Document::parse($html)->hasElement('img'), 'Comment content must not become live markup');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function abruptCommentProvider(): array
    {
        return [
            '<!-->' => ['<!--><img src=x onerror=alert(1)>-->'],
            '<!--->' => ['<!---><img src=x onerror=alert(1)>-->'],
        ];
    }

    public function testCommentsAreKeptByDefault(): void
    {
        $node = $this->parser->parse('<mjml><mj-body><!-- note --></mj-body></mjml>');

        $comment = $node->children[0]->children[0];
        $this->assertSame(Raw::getComponentName(), $comment->tagName);
        $this->assertSame('<!-- note -->', $comment->content);
    }

    public function testCommentsAreDroppedWhenNotKept(): void
    {
        $node = $this->parser->parse('<mjml><mj-body><!--[if mso]><b>x</b><![endif]--></mj-body></mjml>', keepComments: false);

        $this->assertSame([], $node->children[0]->children);
    }

    public function testRendererHonoursKeepCommentsOption(): void
    {
        $mjml = '<mjml><mj-body><!--[if mso]><b>outlook-only</b><![endif]--></mj-body></mjml>';

        $this->assertStringContainsString('outlook-only', Mjml2Html::create()->render($mjml)->html);
        $this->assertStringNotContainsString(
            'outlook-only',
            Mjml2Html::create()->render($mjml, new RenderOptions(keepComments: false))->html
        );
    }

    public function testUnclosedEndingTagDoesNotPreventLaterExtraction(): void
    {
        // The unclosed <mj-button> is left to the XML parser (as an empty element), while the
        // later mj-text content - which is not valid XML - must still be extracted.
        $node = $this->parser->parse(
            '<mjml><mj-body><mj-button/><mj-text>a <br> b</mj-text></mj-body></mjml>'
        );

        $this->assertSame('a <br> b', $node->children[0]->children[1]->content);
    }

    public function testManyUnclosedEndingTagsAreParsedInLinearTime(): void
    {
        $mjml = '<mjml><mj-body>'.str_repeat('<mj-text>', 100000).'</mj-body></mjml>';

        $start = hrtime(true);
        try {
            $this->parser->parse($mjml);
        } catch (ParserException) {
            // Invalid XML is expected; only the time spent extracting matters
        }
        $elapsedSeconds = (hrtime(true) - $start) / 1e9;

        // The previous regex needed ~15s for 80,000 unclosed tags
        $this->assertLessThan(2.0, $elapsedSeconds);
    }

    public function testTagNamePrefixIsNotTreatedAsEndingTag(): void
    {
        // <mj-textual> is not <mj-text>: its content must not be swallowed as ending tag content
        $node = $this->parser->parse('<mjml><mj-body><mj-textual><mj-text>a</mj-text></mj-textual></mj-body></mjml>');

        $unknown = $node->children[0]->children[0];
        $this->assertSame('mj-textual', $unknown->tagName);
        $this->assertSame('a', $unknown->children[0]->content);
    }
}
