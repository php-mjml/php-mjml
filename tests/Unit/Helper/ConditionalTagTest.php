<?php

declare(strict_types=1);

namespace PhpMjml\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use PhpMjml\Helper\ConditionalTag;

final class ConditionalTagTest extends TestCase
{
    public function testWrapContent(): void
    {
        $result = ConditionalTag::wrap('<table></table>');

        $this->assertStringContainsString('<!--[if mso | IE]>', $result);
        $this->assertStringContainsString('<table></table>', $result);
        $this->assertStringContainsString('<![endif]-->', $result);
    }

    public function testWrapNegation(): void
    {
        $result = ConditionalTag::wrap('<div></div>', negation: true);

        $this->assertStringContainsString('<!--[if !mso | IE]><!-->', $result);
        $this->assertStringContainsString('<div></div>', $result);
        $this->assertStringContainsString('<!--<![endif]-->', $result);
    }

    public function testWrapMso(): void
    {
        $result = ConditionalTag::wrapMso('<table></table>');

        $this->assertStringContainsString('<!--[if mso]>', $result);
        $this->assertStringContainsString('<table></table>', $result);
        $this->assertStringContainsString('<![endif]-->', $result);
    }

    public function testWrapMsoNegation(): void
    {
        $result = ConditionalTag::wrapMso('<div></div>', negation: true);

        $this->assertStringContainsString('<!--[if !mso]><!-->', $result);
        $this->assertStringContainsString('<div></div>', $result);
        $this->assertStringContainsString('<!--<![endif]-->', $result);
    }

    public function testConstants(): void
    {
        $this->assertSame('<!--[if mso | IE]>', ConditionalTag::START_CONDITIONAL);
        $this->assertSame('<![endif]-->', ConditionalTag::END_CONDITIONAL);
        $this->assertSame('<!--[if mso]>', ConditionalTag::START_MSO_CONDITIONAL);
        $this->assertSame('<!--[if !mso]><!-->', ConditionalTag::START_MSO_NEGATION);
        $this->assertSame('<!--<![endif]-->', ConditionalTag::END_NEGATION_CONDITIONAL);
    }
}
