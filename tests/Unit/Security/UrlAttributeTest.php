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

use PhpMjml\Renderer\Mjml2Html;
use PhpMjml\Renderer\RenderOptions;
use PhpMjml\Renderer\ValidationException;
use PhpMjml\Security\UrlValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UrlAttributeTest extends TestCase
{
    private const WRAPPER = '<mjml><mj-body><mj-section><mj-column>%s</mj-column></mj-section></mj-body></mjml>';

    private Mjml2Html $renderer;

    protected function setUp(): void
    {
        $this->renderer = Mjml2Html::create();
    }

    #[DataProvider('dangerousUrlProvider')]
    public function testDangerousUrlsAreRemovedByDefault(string $element, string $expectedError): void
    {
        $result = $this->renderer->render(\sprintf(self::WRAPPER, $element));

        $this->assertStringNotContainsStringIgnoringCase('script:', $result->html);
        $this->assertContains($expectedError, $result->errors);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function dangerousUrlProvider(): array
    {
        return [
            'button href' => ['<mj-button href="javascript:alert(1)">b</mj-button>', 'mj-button: Removed "href" because its URL scheme is not allowed.'],
            'image src' => ['<mj-image src="javascript:alert(1)" />', 'mj-image: Removed "src" because its URL scheme is not allowed.'],
            'image href with tab' => ['<mj-image src="https://a/b.png" href="java&#9;script:alert(1)" />', 'mj-image: Removed "href" because its URL scheme is not allowed.'],
            'srcset candidate' => ['<mj-image src="https://a/b.png" srcset="https://a/b.png 1x, javascript:x 2x" />', 'mj-image: Removed "srcset" because its URL scheme is not allowed.'],
            'navbar base-url joined with href' => ['<mj-navbar base-url="java"><mj-navbar-link href="script:alert(1)">a</mj-navbar-link></mj-navbar>', 'mj-navbar-link: Removed "href" because its URL scheme is not allowed.'],
            'vbscript' => ['<mj-button href="VBScript:x">b</mj-button>', 'mj-button: Removed "href" because its URL scheme is not allowed.'],
        ];
    }

    public function testDangerousUrlFromMjAttributesIsRemoved(): void
    {
        $result = $this->renderer->render(
            '<mjml><mj-head><mj-attributes><mj-button href="javascript:alert(1)" /></mj-attributes></mj-head>'
            .\sprintf(substr(self::WRAPPER, 6), '<mj-button>b</mj-button>')
        );

        $this->assertStringNotContainsString('javascript:', $result->html);
    }

    public function testSafeUrlsAreKept(): void
    {
        $result = $this->renderer->render(\sprintf(
            self::WRAPPER,
            '<mj-button href="https://example.com/a?b=c">b</mj-button><mj-image src="cid:logo" href="mailto:a@b.c" /><mj-image src="data:image/png;base64,AAAA" />'
        ));

        $this->assertSame([], $result->errors);
        $this->assertStringContainsString('href="https://example.com/a?b=c"', $result->html);
        $this->assertStringContainsString('src="cid:logo"', $result->html);
        $this->assertStringContainsString('src="data:image/png;base64,AAAA"', $result->html);
    }

    public function testUrlValidationCanBeDisabled(): void
    {
        $html = $this->renderer->render(
            \sprintf(self::WRAPPER, '<mj-button href="javascript:alert(1)">b</mj-button>'),
            new RenderOptions(urlValidator: null)
        )->html;

        $this->assertStringContainsString('href="javascript:alert(1)"', $html);
    }

    public function testStrictModeRejectsDisallowedUrls(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('mj-button: The option "href" with value "javascript:alert(1)" uses a URL scheme that is not allowed.');

        $this->renderer->render(
            \sprintf(self::WRAPPER, '<mj-button href="javascript:alert(1)">b</mj-button>'),
            RenderOptions::forUntrustedInput()
        );
    }

    public function testCustomValidatorCanAllowAdditionalSchemes(): void
    {
        $options = new RenderOptions(
            validationLevel: RenderOptions::forUntrustedInput()->validationLevel,
            urlValidator: new UrlValidator(['https', 'cid']),
        );

        $result = $this->renderer->render(\sprintf(self::WRAPPER, '<mj-image src="cid:logo" />'), $options);

        $this->assertStringContainsString('src="cid:logo"', $result->html);
    }

    public function testFontHrefIsValidated(): void
    {
        $html = $this->renderer->render(
            '<mjml><mj-head><mj-font name="Evil" href="javascript:alert(1)" /></mj-head>'
            .\sprintf(substr(self::WRAPPER, 6), '<mj-text font-family="Evil">x</mj-text>')
        )->html;

        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function testBackgroundUrlCannotCloseCssUrl(): void
    {
        $html = $this->renderer->render(
            "<mjml><mj-body><mj-section background-url=\"https://a/b.png');color:red;x:url('\"><mj-column /></mj-section></mj-body></mjml>"
        )->html;

        $this->assertStringNotContainsString("');color:red", $html);
        $this->assertStringContainsString("url('https://a/b.png%27%29;color:red;x:url%28%27')", $html);
    }
}
