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
 * 1. Converts HTML named entities to numeric equivalents
 * 2. Escapes bare ampersands that are not part of valid entity references
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
        // First, convert known HTML entities to numeric
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
}
