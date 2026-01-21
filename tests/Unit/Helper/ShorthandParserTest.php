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

use PhpMjml\Helper\ShorthandParser;
use PHPUnit\Framework\TestCase;

final class ShorthandParserTest extends TestCase
{
    public function testParseSingleValue(): void
    {
        $this->assertSame(10, ShorthandParser::parse('10px', 'top'));
        $this->assertSame(10, ShorthandParser::parse('10px', 'right'));
        $this->assertSame(10, ShorthandParser::parse('10px', 'bottom'));
        $this->assertSame(10, ShorthandParser::parse('10px', 'left'));
    }

    public function testParseTwoValues(): void
    {
        // "10px 20px" means top/bottom=10, left/right=20
        $this->assertSame(10, ShorthandParser::parse('10px 20px', 'top'));
        $this->assertSame(20, ShorthandParser::parse('10px 20px', 'right'));
        $this->assertSame(10, ShorthandParser::parse('10px 20px', 'bottom'));
        $this->assertSame(20, ShorthandParser::parse('10px 20px', 'left'));
    }

    public function testParseThreeValues(): void
    {
        // "10px 20px 30px" means top=10, left/right=20, bottom=30
        $this->assertSame(10, ShorthandParser::parse('10px 20px 30px', 'top'));
        $this->assertSame(20, ShorthandParser::parse('10px 20px 30px', 'right'));
        $this->assertSame(30, ShorthandParser::parse('10px 20px 30px', 'bottom'));
        $this->assertSame(20, ShorthandParser::parse('10px 20px 30px', 'left'));
    }

    public function testParseFourValues(): void
    {
        // "10px 20px 30px 40px" means top=10, right=20, bottom=30, left=40
        $this->assertSame(10, ShorthandParser::parse('10px 20px 30px 40px', 'top'));
        $this->assertSame(20, ShorthandParser::parse('10px 20px 30px 40px', 'right'));
        $this->assertSame(30, ShorthandParser::parse('10px 20px 30px 40px', 'bottom'));
        $this->assertSame(40, ShorthandParser::parse('10px 20px 30px 40px', 'left'));
    }

    public function testParseEmptyString(): void
    {
        $this->assertSame(0, ShorthandParser::parse('', 'top'));
    }

    public function testParseWithExtraWhitespace(): void
    {
        $this->assertSame(10, ShorthandParser::parse('  10px   20px  ', 'top'));
        $this->assertSame(20, ShorthandParser::parse('  10px   20px  ', 'left'));
    }
}
