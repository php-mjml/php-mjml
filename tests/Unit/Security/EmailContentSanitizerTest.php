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

namespace PhpMjml\Tests\Unit\Security;

use PhpMjml\Security\EmailContentSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EmailContentSanitizerTest extends TestCase
{
    private EmailContentSanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new EmailContentSanitizer();
    }

    public function testSanitizeRemovesScriptTags(): void
    {
        $input = '<script>alert("XSS")</script><p>Safe content</p>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringNotContainsString('alert', $output);
        $this->assertStringContainsString('<p>Safe content</p>', $output);
    }

    public function testSanitizeRemovesEventHandlers(): void
    {
        $input = '<img src="https://example.com/image.jpg" onerror="alert(1)">';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringNotContainsString('onerror', $output);
        $this->assertStringContainsString('src="https://example.com/image.jpg"', $output);
    }

    public function testSanitizeRemovesJavascriptUrls(): void
    {
        $input = '<a href="javascript:alert(1)">Click me</a>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringNotContainsString('javascript:', $output);
        $this->assertStringContainsString('Click me', $output);
    }

    public function testSanitizeAllowsSafeLinks(): void
    {
        $input = '<a href="https://example.com">Safe link</a>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringContainsString('href="https://example.com"', $output);
        $this->assertStringContainsString('Safe link', $output);
    }

    public function testSanitizeAllowsMailtoLinks(): void
    {
        $input = '<a href="mailto:test@example.com">Email us</a>';
        $output = $this->sanitizer->sanitize($input);

        // The sanitizer may encode the @ symbol as an HTML entity
        $this->assertMatchesRegularExpression('/href="mailto:test(@|&#64;)example\.com"/', $output);
        $this->assertStringContainsString('Email us', $output);
    }

    public function testSanitizePreservesTextFormatting(): void
    {
        $input = '<p><strong>Bold</strong> and <em>italic</em> text</p>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringContainsString('<strong>Bold</strong>', $output);
        $this->assertStringContainsString('<em>italic</em>', $output);
    }

    public function testSanitizeRemovesIframes(): void
    {
        $input = '<iframe src="https://evil.com"></iframe><p>Content</p>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringNotContainsString('<iframe', $output);
        $this->assertStringContainsString('<p>Content</p>', $output);
    }

    public function testSanitizeRemovesStyleTags(): void
    {
        $input = '<style>body { display: none; }</style><p>Content</p>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringNotContainsString('<style>', $output);
        $this->assertStringNotContainsString('display: none', $output);
    }

    public function testSanitizeAllowsInlineStyles(): void
    {
        $input = '<p style="color: red;">Red text</p>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringContainsString('style="color: red;"', $output);
    }

    public function testSanitizeAllowsLists(): void
    {
        $input = '<ul><li>Item 1</li><li>Item 2</li></ul>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringContainsString('<ul>', $output);
        $this->assertStringContainsString('<li>Item 1</li>', $output);
        $this->assertStringContainsString('<li>Item 2</li>', $output);
    }

    public function testSanitizeAllowsTables(): void
    {
        $input = '<table><tr><td>Cell 1</td><td>Cell 2</td></tr></table>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringContainsString('<table>', $output);
        $this->assertStringContainsString('<tr>', $output);
        $this->assertStringContainsString('<td>Cell 1</td>', $output);
    }

    public function testSanitizeRemovesDataUrls(): void
    {
        $input = '<img src="data:text/html,<script>alert(1)</script>">';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringNotContainsString('data:', $output);
    }

    public function testSanitizeRemovesFormElements(): void
    {
        $input = '<form action="/submit"><input type="text" name="evil"><button>Submit</button></form>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringNotContainsString('<form', $output);
        $this->assertStringNotContainsString('<input', $output);
        $this->assertStringNotContainsString('<button', $output);
    }

    #[DataProvider('xssVectorsProvider')]
    public function testSanitizeBlocksXssVectors(string $xssVector, string $description): void
    {
        $output = $this->sanitizer->sanitize($xssVector);

        // The sanitized output should not contain any executable JavaScript
        $this->assertStringNotContainsString('<script', strtolower($output), "Failed for: {$description}");
        $this->assertStringNotContainsString('javascript:', strtolower($output), "Failed for: {$description}");
        $this->assertStringNotContainsString('onerror=', strtolower($output), "Failed for: {$description}");
        $this->assertStringNotContainsString('onclick=', strtolower($output), "Failed for: {$description}");
        $this->assertStringNotContainsString('onload=', strtolower($output), "Failed for: {$description}");
        $this->assertStringNotContainsString('onmouseover=', strtolower($output), "Failed for: {$description}");
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function xssVectorsProvider(): array
    {
        return [
            'basic script tag' => [
                '<script>alert("XSS")</script>',
                'Basic script tag injection',
            ],
            'script with attributes' => [
                '<script type="text/javascript">alert(1)</script>',
                'Script tag with type attribute',
            ],
            'img onerror' => [
                '<img src=x onerror=alert(1)>',
                'Image tag with onerror handler',
            ],
            'svg onload' => [
                '<svg onload=alert(1)>',
                'SVG tag with onload handler',
            ],
            'body onload' => [
                '<body onload=alert(1)>',
                'Body tag with onload handler',
            ],
            'javascript url in href' => [
                '<a href="javascript:alert(1)">click</a>',
                'JavaScript URL in anchor href',
            ],
            'javascript url in img src' => [
                '<img src="javascript:alert(1)">',
                'JavaScript URL in image src',
            ],
            'data url with html' => [
                '<a href="data:text/html,<script>alert(1)</script>">click</a>',
                'Data URL with embedded HTML/JS',
            ],
            'vbscript url' => [
                '<a href="vbscript:msgbox(1)">click</a>',
                'VBScript URL scheme',
            ],
            'encoded javascript url' => [
                '<a href="&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;alert(1)">click</a>',
                'HTML entity encoded JavaScript URL',
            ],
            'mixed case javascript' => [
                '<a href="JaVaScRiPt:alert(1)">click</a>',
                'Mixed case JavaScript URL',
            ],
            'event handler with encoding' => [
                '<img src=x onerror="&#97;&#108;&#101;&#114;&#116;&#40;1&#41;">',
                'Event handler with HTML entity encoding',
            ],
            'style expression' => [
                '<div style="width: expression(alert(1))">',
                'CSS expression in style attribute',
            ],
            'iframe injection' => [
                '<iframe src="https://evil.com/steal-cookies.php"></iframe>',
                'Iframe injection',
            ],
            'object tag' => [
                '<object data="data:text/html,<script>alert(1)</script>">',
                'Object tag with data URL',
            ],
            'embed tag' => [
                '<embed src="https://evil.com/malware.swf">',
                'Embed tag injection',
            ],
        ];
    }

    public function testSanitizePreservesContent(): void
    {
        $input = '<p>Hello, <strong>World</strong>!</p>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertSame('<p>Hello, <strong>World</strong>!</p>', $output);
    }

    public function testSanitizeForContextReturnsBodyContent(): void
    {
        $input = '<html><head><title>Test</title></head><body><p>Content</p></body></html>';
        $output = $this->sanitizer->sanitizeForContext($input, 'body');

        $this->assertStringContainsString('<p>Content</p>', $output);
        $this->assertStringNotContainsString('<html>', $output);
        $this->assertStringNotContainsString('<title>', $output);
    }

    public function testStrictConfigOnlyAllowsBasicFormatting(): void
    {
        $sanitizer = new EmailContentSanitizer(EmailContentSanitizer::createStrictConfig());

        // Test with simpler content that strict mode can handle
        $input = '<p style="color: red;">Text</p><strong>Bold</strong>';
        $output = $sanitizer->sanitize($input);

        // Strict config should strip style attributes but keep basic elements
        $this->assertStringNotContainsString('style=', $output);
        $this->assertStringContainsString('Text', $output);
        $this->assertStringContainsString('<strong>Bold</strong>', $output);
    }

    public function testEmptyInputReturnsEmpty(): void
    {
        $this->assertSame('', $this->sanitizer->sanitize(''));
    }

    public function testPlainTextPassesThrough(): void
    {
        $input = 'Just plain text without any HTML';
        $output = $this->sanitizer->sanitize($input);

        $this->assertSame($input, $output);
    }

    public function testSanitizeAllowsHeadings(): void
    {
        $input = '<h1>Title</h1><h2>Subtitle</h2><h3>Section</h3>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringContainsString('<h1>Title</h1>', $output);
        $this->assertStringContainsString('<h2>Subtitle</h2>', $output);
        $this->assertStringContainsString('<h3>Section</h3>', $output);
    }

    public function testSanitizeAllowsBlockquote(): void
    {
        $input = '<blockquote>A famous quote</blockquote>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringContainsString('<blockquote>A famous quote</blockquote>', $output);
    }

    public function testSanitizeAllowsCodeElements(): void
    {
        $input = '<pre><code>function test() {}</code></pre>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringContainsString('<pre>', $output);
        $this->assertStringContainsString('<code>', $output);
        $this->assertStringContainsString('function test() {}', $output);
    }

    public function testSanitizeRemovesMetaTags(): void
    {
        $input = '<meta http-equiv="refresh" content="0;url=https://evil.com"><p>Content</p>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringNotContainsString('<meta', $output);
        $this->assertStringContainsString('<p>Content</p>', $output);
    }

    public function testSanitizeRemovesBaseTags(): void
    {
        $input = '<base href="https://evil.com"><a href="/page">Link</a>';
        $output = $this->sanitizer->sanitize($input);

        $this->assertStringNotContainsString('<base', $output);
    }
}
