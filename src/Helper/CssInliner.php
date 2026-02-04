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

use Symfony\Component\DomCrawler\Crawler;

/**
 * Inlines CSS rules into matching HTML elements' style attributes.
 *
 * Mimics the behavior of the Juice library used by the JS MJML implementation.
 */
final class CssInliner
{
    /**
     * Inline CSS rules into matching HTML elements.
     *
     * @param string       $html       The HTML to process
     * @param list<string> $cssStrings Raw CSS strings to inline
     * @param list<string> $errors     Collects any errors encountered
     */
    public static function inline(string $html, array $cssStrings, array &$errors): string
    {
        $rules = [];
        foreach ($cssStrings as $css) {
            $rules = array_merge($rules, self::parseCssRules($css));
        }

        if ([] === $rules) {
            return $html;
        }

        $wrappedHtml = '<mjml-root xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">'.$html.'</mjml-root>';
        $crawler = new Crawler($wrappedHtml);

        foreach ($rules as $rule) {
            try {
                $crawler->filter($rule['selector'])->each(static function (Crawler $node) use ($rule): void {
                    $domNode = $node->getNode(0);
                    if ($domNode instanceof \DOMElement) {
                        $existing = $domNode->getAttribute('style');
                        $merged = self::mergeStyles($existing, $rule['declarations']);
                        $domNode->setAttribute('style', $merged);
                    }
                });
            } catch (\InvalidArgumentException|\Symfony\Component\CssSelector\Exception\SyntaxErrorException $e) {
                $errors[] = \sprintf(
                    'mj-style inline: Invalid CSS selector "%s" - %s',
                    $rule['selector'],
                    $e->getMessage()
                );
            }
        }

        return $crawler->filter('mjml-root')->html();
    }

    /**
     * Parse CSS text into an array of selector/declarations pairs.
     *
     * @return list<array{selector: string, declarations: string}>
     */
    public static function parseCssRules(string $css): array
    {
        // Strip CSS comments
        $css = (string) preg_replace('/\/\*.*?\*\//s', '', $css);

        $rules = [];

        if (!preg_match_all('/([^{]+)\{([^}]*)\}/s', $css, $matches, \PREG_SET_ORDER)) {
            return [];
        }

        foreach ($matches as $match) {
            $selectors = $match[1];
            $declarations = trim($match[2]);

            if ('' === $declarations) {
                continue;
            }

            // Handle comma-separated selectors
            foreach (explode(',', $selectors) as $selector) {
                $selector = trim($selector);
                if ('' !== $selector) {
                    $rules[] = [
                        'selector' => $selector,
                        'declarations' => $declarations,
                    ];
                }
            }
        }

        return $rules;
    }

    /**
     * Merge new CSS declarations into existing inline style.
     *
     * Existing inline properties take precedence (higher specificity).
     */
    public static function mergeStyles(string $existing, string $new): string
    {
        $existingProps = self::parseDeclarations($existing);
        $newProps = self::parseDeclarations($new);

        // New properties are added only if not already defined inline
        $merged = array_merge($newProps, $existingProps);

        $parts = [];
        foreach ($merged as $prop => $value) {
            $parts[] = "{$prop}: {$value}";
        }

        return implode('; ', $parts).';';
    }

    /**
     * Parse CSS declarations string into an associative array.
     *
     * @return array<string, string>
     */
    public static function parseDeclarations(string $declarations): array
    {
        $result = [];

        $parts = explode(';', $declarations);
        foreach ($parts as $part) {
            $part = trim($part);
            if ('' === $part) {
                continue;
            }

            $colonPos = strpos($part, ':');
            if (false === $colonPos) {
                continue;
            }

            $prop = trim(substr($part, 0, $colonPos));
            $value = trim(substr($part, $colonPos + 1));

            if ('' !== $prop && '' !== $value) {
                $result[$prop] = $value;
            }
        }

        return $result;
    }
}
