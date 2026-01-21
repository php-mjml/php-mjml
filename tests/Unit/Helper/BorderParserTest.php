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

use PhpMjml\Helper\BorderParser;
use PHPUnit\Framework\TestCase;

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
