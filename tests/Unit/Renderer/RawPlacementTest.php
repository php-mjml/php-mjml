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

namespace PhpMjml\Tests\Unit\Renderer;

use PhpMjml\Renderer\Mjml2Html;
use PHPUnit\Framework\TestCase;

/**
 * Tests mj-raw placement outside of mj-body.
 */
final class RawPlacementTest extends TestCase
{
    private const BODY = '<mj-body><mj-section><mj-column><mj-text>Hi</mj-text></mj-column></mj-section></mj-body>';

    private Mjml2Html $renderer;

    protected function setUp(): void
    {
        $this->renderer = Mjml2Html::create();
    }

    public function testFileStartRawIsOutputBeforeDoctype(): void
    {
        $html = $this->renderer->render('<mjml><mj-raw position="file-start">{% set foo = 1 %}</mj-raw>'.self::BODY.'</mjml>')->html;

        $this->assertStringStartsWith("{% set foo = 1 %}\n<!doctype html>", $html);
    }

    public function testMultipleFileStartRawsAreJoinedInOrder(): void
    {
        $html = $this->renderer->render(
            '<mjml><mj-raw position="file-start">A</mj-raw><mj-raw position="file-start">B</mj-raw>'.self::BODY.'</mjml>'
        )->html;

        $this->assertStringStartsWith("A\nB\n<!doctype html>", $html);
    }

    public function testRootRawWithoutPositionIsIgnored(): void
    {
        $html = $this->renderer->render('<mjml><mj-raw>ignored-raw</mj-raw>'.self::BODY.'</mjml>')->html;

        $this->assertStringStartsWith('<!doctype html>', $html);
        $this->assertStringNotContainsString('ignored-raw', $html);
    }

    public function testHeadRawIsOutputAtEndOfHead(): void
    {
        $html = $this->renderer->render(
            '<mjml><mj-head><mj-raw><meta name="x-custom" content="1"></mj-raw></mj-head>'.self::BODY.'</mjml>'
        )->html;

        $this->assertMatchesRegularExpression('#<meta name="x-custom" content="1">\s*</head>#', $html);
    }
}
