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

namespace PhpMjml\Security;

/**
 * URL scheme validator to prevent injection of dangerous URLs.
 *
 * This validator checks URLs against a list of allowed schemes to prevent
 * XSS attacks via javascript:, data:, and other dangerous URL schemes.
 *
 * Usage:
 *     $validator = new UrlValidator();
 *     if ($validator->isValid($url)) {
 *         // URL is safe to use
 *     }
 *
 *     // Or use assertValid() which throws an exception for invalid URLs
 *     $validator->assertValid($url);
 */
final class UrlValidator
{
    /**
     * Dangerous URL schemes that should be blocked.
     *
     * @var list<string>
     */
    private const array DANGEROUS_SCHEMES = [
        'javascript',
        'vbscript',
        'data',
        'file',
        'mhtml',
        'x-javascript',
    ];

    /**
     * Default allowed URL schemes.
     *
     * @var list<string>
     */
    private const array DEFAULT_ALLOWED_SCHEMES = [
        'https',
        'http',
        'mailto',
        'tel',
    ];

    /**
     * @param list<string> $allowedSchemes Schemes that are explicitly allowed
     */
    public function __construct(
        private readonly array $allowedSchemes = self::DEFAULT_ALLOWED_SCHEMES,
    ) {
    }

    /**
     * Check if a URL is safe to use.
     *
     * @param string $url The URL to validate
     *
     * @return bool True if the URL is safe, false otherwise
     */
    public function isValid(string $url): bool
    {
        $url = trim($url);

        // Empty URLs are considered safe (they do nothing)
        if ('' === $url) {
            return true;
        }

        // Relative URLs without scheme are safe
        if ($this->isRelativeUrl($url)) {
            return true;
        }

        $scheme = $this->extractScheme($url);

        // No scheme found - treat as relative URL, which is safe
        if (null === $scheme) {
            return true;
        }

        $scheme = strtolower($scheme);

        // Check if scheme is in the dangerous list
        if (\in_array($scheme, self::DANGEROUS_SCHEMES, true)) {
            return false;
        }

        // If we have allowed schemes configured, check against them
        if ([] !== $this->allowedSchemes) {
            return \in_array($scheme, array_map('strtolower', $this->allowedSchemes), true);
        }

        return true;
    }

    /**
     * Assert that a URL is safe to use.
     *
     * @param string $url The URL to validate
     *
     * @throws InvalidUrlException If the URL is not safe
     */
    public function assertValid(string $url): void
    {
        if (!$this->isValid($url)) {
            throw new InvalidUrlException(\sprintf('URL "%s" contains a potentially dangerous scheme.', $this->truncateUrl($url)));
        }
    }

    /**
     * Sanitize a URL by returning an empty string if it's invalid.
     */
    public function sanitize(string $url): string
    {
        return $this->isValid($url) ? $url : '';
    }

    /**
     * Create a validator that only allows HTTPS URLs.
     */
    public static function httpsOnly(): self
    {
        return new self(['https']);
    }

    /**
     * Create a validator for web URLs (HTTP and HTTPS).
     */
    public static function webUrls(): self
    {
        return new self(['https', 'http']);
    }

    /**
     * Create a validator with the default allowed schemes.
     */
    public static function default(): self
    {
        return new self();
    }

    /**
     * Check if a URL is relative (no scheme).
     */
    private function isRelativeUrl(string $url): bool
    {
        // URLs starting with / or . are relative
        if (str_starts_with($url, '/') || str_starts_with($url, '.')) {
            return true;
        }

        // URLs starting with # are fragment-only
        if (str_starts_with($url, '#')) {
            return true;
        }

        // URLs starting with ? are query-only
        if (str_starts_with($url, '?')) {
            return true;
        }

        return false;
    }

    /**
     * Extract the scheme from a URL.
     *
     * @return string|null The scheme (without colon) or null if no scheme found
     */
    private function extractScheme(string $url): ?string
    {
        // Look for scheme:// or scheme: pattern
        // Scheme must start with a letter and contain only letters, digits, +, -, .
        if (preg_match('/^([a-zA-Z][a-zA-Z0-9+.-]*):/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Truncate a URL for safe display in error messages.
     */
    private function truncateUrl(string $url): string
    {
        $maxLength = 100;

        if (\strlen($url) <= $maxLength) {
            return $url;
        }

        return substr($url, 0, $maxLength).'...';
    }
}
