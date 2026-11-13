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

namespace PhpMjml\Tests\Unit\Helper;

use PhpMjml\Helper\CssHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CssHelperTest extends TestCase
{
    #[DataProvider('legitimateValueProvider')]
    public function testSanitizeValueKeepsLegitimateValues(string $value): void
    {
        $this->assertSame($value, CssHelper::sanitizeValue($value));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function legitimateValueProvider(): array
    {
        return [
            'pixels' => ['480px'],
            'percent' => ['33.5%'],
            'hex' => ['#fead0d'],
            'rgba' => ['rgba(0, 0, 0, 0.5)'],
            'named' => ['transparent'],
        ];
    }

    #[DataProvider('breakoutValueProvider')]
    public function testSanitizeValueRemovesBreakoutCharacters(string $value, string $expected): void
    {
        $this->assertSame($expected, CssHelper::sanitizeValue($value));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function breakoutValueProvider(): array
    {
        return [
            'new rule' => ['red;}.x{a:b', 'red.xa:b'],
            'style end tag' => ['1px</style><script>', '1px/stylescript'],
            'comment' => ['1px/* rest', '1px/ rest'],
            'quotes and escapes' => ['a"b\'c\\d', 'abcd'],
            'newline' => ["1px\n}", '1px'],
        ];
    }

    public function testEscapeUrlKeepsOrdinaryUrls(): void
    {
        $url = 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap';

        $this->assertSame($url, CssHelper::escapeUrl($url));
    }

    public function testEscapeUrlEncodesCharactersThatEndTheUrlToken(): void
    {
        // ";" is left alone: it is valid inside a url token and used by Google Fonts css2 URLs
        $this->assertSame(
            'https://x/a.css%29;%7D.x%7Ba:b%7D%3C/style%3E%20%27%22%5C',
            CssHelper::escapeUrl("https://x/a.css);}.x{a:b}</style> '\"\\")
        );
    }
}
