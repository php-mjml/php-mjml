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

use PhpMjml\Helper\WidthParser;
use PHPUnit\Framework\TestCase;

final class WidthParserTest extends TestCase
{
    public function testParsePixelValue(): void
    {
        $result = WidthParser::parse('200px');

        $this->assertSame(200, $result['parsedWidth']);
        $this->assertSame('px', $result['unit']);
    }

    public function testParsePercentageValue(): void
    {
        $result = WidthParser::parse('50%');

        $this->assertSame(50, $result['parsedWidth']);
        $this->assertSame('%', $result['unit']);
    }

    public function testParsePercentageWithFloat(): void
    {
        $result = WidthParser::parse('33.33%', parseFloatToInt: false);

        $this->assertSame(33.33, $result['parsedWidth']);
        $this->assertSame('%', $result['unit']);
    }

    public function testParseValueWithoutUnit(): void
    {
        $result = WidthParser::parse('600');

        $this->assertSame(600, $result['parsedWidth']);
        $this->assertSame('px', $result['unit']);
    }

    public function testParseEmValue(): void
    {
        $result = WidthParser::parse('10em');

        $this->assertSame(10, $result['parsedWidth']);
        $this->assertSame('em', $result['unit']);
    }
}
