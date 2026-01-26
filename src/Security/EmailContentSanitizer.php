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

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Email-optimized HTML sanitizer for MJML content.
 *
 * This sanitizer is designed to clean user-provided HTML content before
 * embedding it in MJML templates. It allows safe HTML elements commonly
 * used in emails while blocking dangerous elements and attributes.
 *
 * Usage:
 *     $sanitizer = new EmailContentSanitizer();
 *     $safeHtml = $sanitizer->sanitize($untrustedHtml);
 */
final class EmailContentSanitizer
{
    private const int DEFAULT_MAX_INPUT_LENGTH = 50000;

    private HtmlSanitizer $sanitizer;

    public function __construct(?HtmlSanitizerConfig $config = null)
    {
        $this->sanitizer = new HtmlSanitizer(
            $config ?? self::createDefaultConfig()
        );
    }

    /**
     * Sanitize HTML content for safe use in email templates.
     */
    public function sanitize(string $html): string
    {
        return $this->sanitizer->sanitize($html);
    }

    /**
     * Sanitize HTML content, preserving only body content.
     * Use this for content that will be embedded inside another HTML document.
     */
    public function sanitizeForContext(string $html, string $context = 'body'): string
    {
        return $this->sanitizer->sanitizeFor($context, $html);
    }

    /**
     * Creates the default sanitizer configuration optimized for email content.
     *
     * Allowed elements:
     * - Text formatting: p, br, strong, b, em, i, u, s, span, sub, sup
     * - Headings: h1-h6
     * - Links: a (with href, title, target attributes)
     * - Lists: ul, ol, li
     * - Tables: table, tr, td, th, thead, tbody, tfoot
     * - Other: blockquote, hr, pre, code
     *
     * Blocked elements:
     * - Scripts: script, noscript
     * - Styles: style (inline styles on elements are allowed)
     * - Frames: iframe, frame, frameset
     * - Objects: object, embed, applet
     * - Forms: form, input, button, select, textarea
     *
     * URL schemes allowed: https, http, mailto
     */
    public static function createDefaultConfig(): HtmlSanitizerConfig
    {
        return (new HtmlSanitizerConfig())
            // Text formatting elements
            ->allowElement('p', ['style', 'class'])
            ->allowElement('br')
            ->allowElement('strong')
            ->allowElement('b')
            ->allowElement('em')
            ->allowElement('i')
            ->allowElement('u')
            ->allowElement('s')
            ->allowElement('span', ['style', 'class'])
            ->allowElement('sub')
            ->allowElement('sup')

            // Headings
            ->allowElement('h1', ['style', 'class'])
            ->allowElement('h2', ['style', 'class'])
            ->allowElement('h3', ['style', 'class'])
            ->allowElement('h4', ['style', 'class'])
            ->allowElement('h5', ['style', 'class'])
            ->allowElement('h6', ['style', 'class'])

            // Links with safe schemes only
            ->allowElement('a', ['href', 'title', 'target', 'style', 'class'])
            ->allowLinkSchemes(['https', 'http', 'mailto'])

            // Lists
            ->allowElement('ul', ['style', 'class'])
            ->allowElement('ol', ['style', 'class'])
            ->allowElement('li', ['style', 'class'])

            // Tables (commonly used in email layouts)
            ->allowElement('table', ['border', 'cellpadding', 'cellspacing', 'width', 'style', 'class'])
            ->allowElement('thead')
            ->allowElement('tbody')
            ->allowElement('tfoot')
            ->allowElement('tr', ['style', 'class'])
            ->allowElement('td', ['colspan', 'rowspan', 'width', 'height', 'style', 'class', 'align', 'valign'])
            ->allowElement('th', ['colspan', 'rowspan', 'width', 'height', 'style', 'class', 'align', 'valign'])

            // Other safe elements
            ->allowElement('blockquote', ['style', 'class'])
            ->allowElement('hr')
            ->allowElement('pre', ['style', 'class'])
            ->allowElement('code', ['style', 'class'])
            ->allowElement('div', ['style', 'class'])

            // Images with safe schemes
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height', 'style', 'class'])
            ->allowMediaSchemes(['https', 'http'])

            // Drop dangerous elements completely (including content)
            ->dropElement('script')
            ->dropElement('noscript')
            ->dropElement('style')
            ->dropElement('iframe')
            ->dropElement('frame')
            ->dropElement('frameset')
            ->dropElement('object')
            ->dropElement('embed')
            ->dropElement('applet')
            ->dropElement('svg')
            // Block form elements (keep content but remove tags)
            ->blockElement('form')
            ->blockElement('input')
            ->blockElement('button')
            ->blockElement('select')
            ->blockElement('textarea')
            ->blockElement('meta')
            ->blockElement('link')
            ->blockElement('base')

            // Set reasonable input length limit
            ->withMaxInputLength(self::DEFAULT_MAX_INPUT_LENGTH);
    }

    /**
     * Creates a strict sanitizer configuration that only allows basic text formatting.
     */
    public static function createStrictConfig(): HtmlSanitizerConfig
    {
        return (new HtmlSanitizerConfig())
            ->allowElement('p')
            ->allowElement('br')
            ->allowElement('strong')
            ->allowElement('b')
            ->allowElement('em')
            ->allowElement('i')
            ->allowElement('u')
            ->allowElement('a', ['href', 'title'])
            ->allowLinkSchemes(['https', 'http', 'mailto'])
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->blockElement('script')
            ->blockElement('style')
            ->withMaxInputLength(self::DEFAULT_MAX_INPUT_LENGTH);
    }

    /**
     * Creates a permissive sanitizer configuration for trusted sources.
     * Use with caution - only for content from verified sources.
     */
    public static function createPermissiveConfig(): HtmlSanitizerConfig
    {
        return self::createDefaultConfig()
            ->allowElement('style')
            ->withMaxInputLength(100000);
    }
}
