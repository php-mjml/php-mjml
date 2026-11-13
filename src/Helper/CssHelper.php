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

namespace PhpMjml\Helper;

final class CssHelper
{
    /**
     * Characters that let a value escape a CSS declaration or a <style> element:
     * rule/declaration delimiters, comments, quotes, escapes, tag brackets and control characters.
     */
    private const UNSAFE_VALUE_CHARS = '/[{};<>"\'\\\\*\x00-\x1F\x7F]/';

    /**
     * Characters that would terminate or escape an unquoted CSS url() token, or close a <style> element.
     * Braces are valid inside a url token but never appear unencoded in real URLs, so they are encoded too.
     */
    private const UNSAFE_URL_CHARS = '/[(){}\'"\\\\<>\s\x00-\x1F\x7F]/';

    /**
     * Make a user-supplied value safe to embed as a CSS declaration value inside a <style> block.
     *
     * Legitimate MJML values (lengths, colors, rgb() functions) are left untouched; anything
     * that could close the declaration, open a new rule or break out of the style element is removed.
     */
    public static function sanitizeValue(string $value): string
    {
        return (string) preg_replace(self::UNSAFE_VALUE_CHARS, '', $value);
    }

    /**
     * Make a URL safe to embed inside an unquoted CSS url() token.
     *
     * Unsafe characters are percent-encoded rather than removed so the URL keeps its meaning.
     */
    public static function escapeUrl(string $url): string
    {
        return (string) preg_replace_callback(
            self::UNSAFE_URL_CHARS,
            static fn (array $match): string => rawurlencode($match[0]),
            $url
        );
    }

    /**
     * Suffix CSS classes with a given suffix.
     *
     * @param string|null $classes Space-separated CSS classes
     * @param string      $suffix  Suffix to append to each class
     *
     * @return string Suffixed classes or empty string
     */
    public static function suffixCssClasses(?string $classes, string $suffix): string
    {
        if (null === $classes || '' === $classes) {
            return '';
        }

        $classList = preg_split('/\s+/', trim($classes));
        if (false === $classList) {
            return '';
        }

        /** @var list<string> $suffixed */
        $suffixed = array_map(static fn (string $c): string => "{$c}-{$suffix}", $classList);

        return implode(' ', $suffixed);
    }
}
