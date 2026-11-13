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

namespace PhpMjml\Tests\Unit\Component;

use PhpMjml\Component\AttributeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AttributeTypeTest extends TestCase
{
    #[DataProvider('validProvider')]
    public function testAcceptsValidValues(string $type, string $value): void
    {
        $this->assertTrue(AttributeType::isValid($type, $value));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function validProvider(): array
    {
        return [
            'short hex' => ['color', '#abc'],
            'long hex' => ['color', '#AABBCC'],
            'named color' => ['color', 'rebeccapurple'],
            'transparent' => ['color', 'transparent'],
            'rgb' => ['color', 'rgb(1,2,3)'],
            'rgba with spaces' => ['color', 'rgba(0, 0, 0, 0.5)'],
            'px' => ['unit(px,%)', '10px'],
            'percent' => ['unit(px,%)', '33.33%'],
            'zero' => ['unit(px,%)', '0'],
            'shorthand' => ['unit(px,%){1,4}', '10px 25px'],
            'four values' => ['unit(px,%){1,4}', '1px 2px 3px 4px'],
            'unitless allowed' => ['unit(px,%,)', '1.5'],
            'auto' => ['unit(px,auto)', 'auto'],
            'negative' => ['unitWithNegative(px,em)', '-2px'],
            'enum' => ['enum(left,center,right)', 'center'],
            'enum empty member' => ['enum(full-width,false,)', ''],
            'integer' => ['integer', '12'],
            'integer with unit (accepted by JS)' => ['integer', '6px'],
            'boolean' => ['boolean', 'TRUE'],
            'string' => ['string', '"anything" <goes>'],
            'empty value' => ['color', ''],
            'unknown type' => ['custom-type', 'whatever'],
        ];
    }

    #[DataProvider('invalidProvider')]
    public function testRejectsInvalidValues(string $type, string $value): void
    {
        $this->assertFalse(AttributeType::isValid($type, $value));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidProvider(): array
    {
        return [
            // The JS matchers are unanchored and would accept these
            'rgb with trailing markup' => ['color', 'rgb(1,2,3)"><script>'],
            'rgba with trailing css' => ['color', 'rgba(0,0,0,0.5);background:url(x)'],
            'integer with trailing markup' => ['integer', '1"><x'],
            'color declaration injection' => ['color', 'red;background:url(x)'],
            'unknown color name' => ['color', 'notacolor'],
            'eight digit hex' => ['color', '#aabbccdd'],
            'wrong unit' => ['unit(px,%)', '10em'],
            'unit breakout' => ['unit(px,%)', '1px" onclick="x'],
            'too many values' => ['unit(px,%){1,4}', '1px 2px 3px 4px 5px'],
            'negative not allowed' => ['unit(px,%)', '-2px'],
            'auto not allowed' => ['unit(px,%)', 'auto'],
            'enum' => ['enum(left,center,right)', 'justify'],
            'enum empty not allowed' => ['enum(left,center,right)', ' '],
            'boolean' => ['boolean', 'yes'],
        ];
    }
}
