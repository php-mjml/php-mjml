<?php

declare(strict_types=1);

namespace PhpMjml\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use PhpMjml\Helper\BorderParser;

final class BorderParserTest extends TestCase
{
    public function testParseBorderShorthand(): void
    {
        $this->assertSame(1, BorderParser::parse('1px solid red'));
        $this->assertSame(2, BorderParser::parse('2px dashed #000'));
        $this->assertSame(10, BorderParser::parse('10px dotted blue'));
    }

    public function testParseBorderWithOnlyWidth(): void
    {
        $this->assertSame(5, BorderParser::parse('5px'));
    }

    public function testParseEmptyBorder(): void
    {
        $this->assertSame(0, BorderParser::parse(''));
        $this->assertSame(0, BorderParser::parse('0'));
    }

    public function testParseNone(): void
    {
        $this->assertSame(0, BorderParser::parse('none'));
    }

    public function testParseBorderWithSpaceBeforeWidth(): void
    {
        $this->assertSame(3, BorderParser::parse('solid 3px red'));
    }
}
