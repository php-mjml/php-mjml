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

namespace PhpMjml\Parser;

/**
 * Default XML preprocessor that handles HTML entity conversion.
 *
 * XML only recognizes &amp; &lt; &gt; &quot; &apos; as named entities.
 * This preprocessor:
 * 1. Removes duplicate attributes from opening tags (XML forbids them, HTML silently keeps the first)
 * 2. Converts HTML named entities to numeric equivalents
 * 3. Escapes bare ampersands that are not part of valid entity references
 */
final class XmlEntityPreprocessor implements XmlPreprocessorInterface
{
    /**
     * HTML named entities mapped to their numeric equivalents.
     * XML only supports &amp; &lt; &gt; &quot; &apos; natively.
     *
     * @var array<string, string>
     */
    private const HTML_ENTITY_MAP = [
        '&nbsp;' => '&#160;',
        '&copy;' => '&#169;',
        '&reg;' => '&#174;',
        '&trade;' => '&#8482;',
        '&mdash;' => '&#8212;',
        '&ndash;' => '&#8211;',
        '&lsquo;' => '&#8216;',
        '&rsquo;' => '&#8217;',
        '&ldquo;' => '&#8220;',
        '&rdquo;' => '&#8221;',
        '&bull;' => '&#8226;',
        '&hellip;' => '&#8230;',
        '&euro;' => '&#8364;',
        '&pound;' => '&#163;',
        '&yen;' => '&#165;',
        '&cent;' => '&#162;',
        '&deg;' => '&#176;',
        '&plusmn;' => '&#177;',
        '&times;' => '&#215;',
        '&divide;' => '&#247;',
        '&frac12;' => '&#189;',
        '&frac14;' => '&#188;',
        '&frac34;' => '&#190;',
    ];

    public function preprocess(string $xml): string
    {
        // Remove duplicate attributes (XML forbids them; HTML keeps the first)
        $xml = $this->deduplicateAttributes($xml);

        // Convert known HTML entities to numeric
        $xml = str_replace(
            array_keys(self::HTML_ENTITY_MAP),
            array_values(self::HTML_ENTITY_MAP),
            $xml
        );

        // Escape bare ampersands that are not part of valid entity references
        // Valid entities: &name; or &#123; or &#x1A;
        // We match & not followed by: word chars + semicolon, or # + digits + semicolon, or #x + hex + semicolon
        $xml = preg_replace(
            '/&(?!(?:[a-zA-Z][a-zA-Z0-9]*|#[0-9]+|#x[0-9a-fA-F]+);)/',
            '&amp;',
            $xml
        ) ?? $xml;

        return $xml;
    }

    /**
     * Remove duplicate attributes from opening tags.
     *
     * XML rejects duplicate attributes as a fatal error. HTML silently keeps
     * the first occurrence. This matches HTML behavior for MJML compatibility.
     */
    private function deduplicateAttributes(string $xml): string
    {
        return preg_replace_callback(
            '/<([a-zA-Z][a-zA-Z0-9-]*)(\s[^>]*?)(\s*\/?)>/s',
            static function (array $match): string {
                $tagName = $match[1];
                $attrString = $match[2];
                $selfClose = $match[3];

                $seen = [];
                $deduplicated = preg_replace_callback(
                    '/(\s+)([a-zA-Z][a-zA-Z0-9-]*)(\s*=\s*"[^"]*"|\s*=\s*\'[^\']*\')?/',
                    static function (array $attrMatch) use (&$seen): string {
                        $name = strtolower($attrMatch[2]);
                        if (isset($seen[$name])) {
                            return '';
                        }
                        $seen[$name] = true;

                        return $attrMatch[0];
                    },
                    $attrString
                );

                return '<'.$tagName.($deduplicated ?? $attrString).$selfClose.'>';
            },
            $xml
        ) ?? $xml;
    }
}
