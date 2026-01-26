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

use PhpMjml\Security\InvalidUrlException;
use PhpMjml\Security\UrlValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UrlValidatorTest extends TestCase
{
    private UrlValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new UrlValidator();
    }

    #[DataProvider('validUrlsProvider')]
    public function testIsValidReturnsTrueForValidUrls(string $url): void
    {
        $this->assertTrue($this->validator->isValid($url));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function validUrlsProvider(): array
    {
        return [
            'https url' => ['https://example.com'],
            'https with path' => ['https://example.com/path/to/page'],
            'https with query' => ['https://example.com?query=value'],
            'https with fragment' => ['https://example.com#section'],
            'https with port' => ['https://example.com:8080/path'],
            'http url' => ['http://example.com'],
            'mailto url' => ['mailto:user@example.com'],
            'mailto with subject' => ['mailto:user@example.com?subject=Hello'],
            'tel url' => ['tel:+1234567890'],
            'relative url with slash' => ['/path/to/page'],
            'relative url with dot' => ['./path/to/page'],
            'relative url parent' => ['../path/to/page'],
            'fragment only' => ['#section'],
            'query only' => ['?query=value'],
            'empty string' => [''],
            'path without leading slash' => ['path/to/page'],
        ];
    }

    #[DataProvider('invalidUrlsProvider')]
    public function testIsValidReturnsFalseForInvalidUrls(string $url): void
    {
        $this->assertFalse($this->validator->isValid($url));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidUrlsProvider(): array
    {
        return [
            'javascript url' => ['javascript:alert(1)'],
            'javascript with padding' => ['javascript:void(0)'],
            'javascript mixed case' => ['JaVaScRiPt:alert(1)'],
            'javascript uppercase' => ['JAVASCRIPT:alert(1)'],
            'vbscript url' => ['vbscript:msgbox(1)'],
            'vbscript mixed case' => ['VbScript:msgbox(1)'],
            'data url' => ['data:text/html,<script>alert(1)</script>'],
            'data url base64' => ['data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg=='],
            'file url' => ['file:///etc/passwd'],
            'mhtml url' => ['mhtml:http://evil.com'],
            'x-javascript url' => ['x-javascript:alert(1)'],
        ];
    }

    public function testAssertValidThrowsForInvalidUrl(): void
    {
        $this->expectException(InvalidUrlException::class);
        $this->expectExceptionMessage('potentially dangerous scheme');

        $this->validator->assertValid('javascript:alert(1)');
    }

    public function testAssertValidDoesNotThrowForValidUrl(): void
    {
        // Should not throw
        $this->validator->assertValid('https://example.com');

        // If we get here, the test passed
        $this->assertTrue(true);
    }

    public function testSanitizeReturnsUrlForValidUrls(): void
    {
        $url = 'https://example.com/path';
        $this->assertSame($url, $this->validator->sanitize($url));
    }

    public function testSanitizeReturnsEmptyStringForInvalidUrls(): void
    {
        $this->assertSame('', $this->validator->sanitize('javascript:alert(1)'));
    }

    public function testHttpsOnlyValidatorRejectsHttp(): void
    {
        $validator = UrlValidator::httpsOnly();

        $this->assertTrue($validator->isValid('https://example.com'));
        $this->assertFalse($validator->isValid('http://example.com'));
    }

    public function testWebUrlsValidatorAllowsHttpAndHttps(): void
    {
        $validator = UrlValidator::webUrls();

        $this->assertTrue($validator->isValid('https://example.com'));
        $this->assertTrue($validator->isValid('http://example.com'));
        $this->assertFalse($validator->isValid('mailto:user@example.com'));
    }

    public function testDefaultFactoryMatchesConstructorDefault(): void
    {
        $validator = UrlValidator::default();

        $this->assertTrue($validator->isValid('https://example.com'));
        $this->assertTrue($validator->isValid('http://example.com'));
        $this->assertTrue($validator->isValid('mailto:user@example.com'));
        $this->assertTrue($validator->isValid('tel:+1234567890'));
    }

    public function testCustomAllowedSchemes(): void
    {
        $validator = new UrlValidator(['ftp', 'sftp']);

        $this->assertTrue($validator->isValid('ftp://files.example.com'));
        $this->assertTrue($validator->isValid('sftp://files.example.com'));
        $this->assertFalse($validator->isValid('https://example.com'));
        $this->assertFalse($validator->isValid('http://example.com'));

        // Relative URLs should still be valid
        $this->assertTrue($validator->isValid('/path/to/file'));

        // Dangerous schemes should still be blocked
        $this->assertFalse($validator->isValid('javascript:alert(1)'));
    }

    public function testUrlWithWhitespace(): void
    {
        // URLs with leading/trailing whitespace should be trimmed
        $this->assertTrue($this->validator->isValid('  https://example.com  '));
        $this->assertFalse($this->validator->isValid('  javascript:alert(1)  '));
    }

    public function testUrlWithSchemeVariations(): void
    {
        // Test various scheme patterns
        $this->assertTrue($this->validator->isValid('https://example.com'));
        $this->assertTrue($this->validator->isValid('HTTPS://example.com'));
        $this->assertTrue($this->validator->isValid('Https://example.com'));
    }

    public function testRelativeUrlsAreAlwaysValid(): void
    {
        // Even with restrictive schemes, relative URLs should pass
        $validator = new UrlValidator(['https']);

        $this->assertTrue($validator->isValid('/absolute/path'));
        $this->assertTrue($validator->isValid('./relative/path'));
        $this->assertTrue($validator->isValid('../parent/path'));
        $this->assertTrue($validator->isValid('#anchor'));
        $this->assertTrue($validator->isValid('?query=value'));
    }

    public function testExceptionMessageTruncatesLongUrls(): void
    {
        $longUrl = 'javascript:'.str_repeat('x', 200);

        try {
            $this->validator->assertValid($longUrl);
            $this->fail('Expected InvalidUrlException to be thrown');
        } catch (InvalidUrlException $e) {
            // Message should be truncated with "..."
            $this->assertStringContainsString('...', $e->getMessage());
            // But still contain useful info
            $this->assertStringContainsString('javascript:', $e->getMessage());
        }
    }

    public function testComplexUrls(): void
    {
        // Complex but valid URLs
        $this->assertTrue($this->validator->isValid('https://user:pass@example.com:8080/path?query=value&other=1#section'));
        $this->assertTrue($this->validator->isValid('mailto:user@example.com?subject=Hello%20World&body=Test'));

        // Valid URL with encoded characters
        $this->assertTrue($this->validator->isValid('https://example.com/path%20with%20spaces'));
    }
}
