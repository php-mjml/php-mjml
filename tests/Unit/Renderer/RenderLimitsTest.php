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
use PhpMjml\Renderer\RenderOptions;
use PhpMjml\Security\LimitExceededException;
use PHPUnit\Framework\TestCase;

final class RenderLimitsTest extends TestCase
{
    private Mjml2Html $renderer;

    protected function setUp(): void
    {
        $this->renderer = Mjml2Html::create();
    }

    public function testInputLengthLimit(): void
    {
        $mjml = '<mjml><mj-body>'.str_repeat(' ', 200).'</mj-body></mjml>';

        $this->expectException(LimitExceededException::class);
        $this->expectExceptionMessage('input length');

        $this->renderer->render($mjml, new RenderOptions(maxInputLength: 100));
    }

    public function testDefaultInputLengthLimitIsApplied(): void
    {
        $mjml = '<mjml><mj-body>'.str_repeat(' ', RenderOptions::DEFAULT_MAX_INPUT_LENGTH).'</mj-body></mjml>';

        $this->expectException(LimitExceededException::class);

        $this->renderer->render($mjml);
    }

    public function testCarouselImageLimit(): void
    {
        $this->expectException(LimitExceededException::class);
        $this->expectExceptionMessage('mj-carousel image count limit: 3 (maximum 2)');

        $this->renderer->render($this->carousel(3), new RenderOptions(maxCarouselImages: 2));
    }

    public function testCarouselWithinLimitRenders(): void
    {
        $html = $this->renderer->render($this->carousel(2), new RenderOptions(maxCarouselImages: 2))->html;

        $this->assertStringContainsString('mj-carousel-image-2', $html);
    }

    public function testInlineCssRuleLimit(): void
    {
        $mjml = '<mjml><mj-head><mj-style inline="inline">.a{color:red} .b{color:blue} .c{color:green}</mj-style></mj-head>'
            .'<mj-body><mj-section><mj-column><mj-text css-class="a">t</mj-text></mj-column></mj-section></mj-body></mjml>';

        $this->expectException(LimitExceededException::class);
        $this->expectExceptionMessage('inline CSS rule count limit: 3 (maximum 2)');

        $this->renderer->render($mjml, new RenderOptions(maxInlineCssRules: 2));
    }

    public function testLimitsCanBeDisabled(): void
    {
        $options = new RenderOptions(maxInputLength: null, maxCarouselImages: null, maxInlineCssRules: null);

        $html = $this->renderer->render($this->carousel(RenderOptions::DEFAULT_MAX_CAROUSEL_IMAGES + 1), $options)->html;

        $this->assertStringContainsString('mj-carousel-image-'.(RenderOptions::DEFAULT_MAX_CAROUSEL_IMAGES + 1), $html);
    }

    private function carousel(int $images): string
    {
        return '<mjml><mj-body><mj-section><mj-column><mj-carousel>'
            .str_repeat('<mj-carousel-image src="https://example.com/a.png" />', $images)
            .'</mj-carousel></mj-column></mj-section></mj-body></mjml>';
    }
}
