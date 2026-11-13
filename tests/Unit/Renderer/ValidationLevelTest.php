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
use PhpMjml\Renderer\ValidationException;
use PhpMjml\Renderer\ValidationLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValidationLevelTest extends TestCase
{
    private const INVALID_COLOR = '<mjml><mj-body><mj-section><mj-column><mj-text color="red;background:url(x)">t</mj-text></mj-column></mj-section></mj-body></mjml>';

    private Mjml2Html $renderer;

    protected function setUp(): void
    {
        $this->renderer = Mjml2Html::create();
    }

    public function testSoftModeReportsTypeViolationsAndRenders(): void
    {
        $result = $this->renderer->render(self::INVALID_COLOR);

        $this->assertSame(
            ['mj-text: The option "color" with value "red;background:url(x)" is invalid for type "color".'],
            $result->errors
        );
        $this->assertStringContainsString('<!doctype html>', $result->html);
    }

    public function testStrictModeThrowsWithAllViolations(): void
    {
        try {
            $this->renderer->render(
                '<mjml><mj-body><mj-section><mj-column><mj-text color="x">a</mj-text><mj-text padding="y">b</mj-text></mj-column></mj-section></mj-body></mjml>',
                new RenderOptions(validationLevel: ValidationLevel::Strict)
            );
            $this->fail('Expected a ValidationException');
        } catch (ValidationException $e) {
            $this->assertCount(2, $e->getErrors());
            $this->assertStringContainsString('"color"', $e->getErrors()[0]);
            $this->assertStringContainsString('"padding"', $e->getErrors()[1]);
        }
    }

    public function testStrictModeAcceptsValidTemplates(): void
    {
        $result = $this->renderer->render(
            '<mjml><mj-head><mj-attributes><mj-all font-family="Arial" padding="0px" /><mj-class name="big" font-size="20px" /></mj-attributes></mj-head>'
            .'<mj-body><mj-section><mj-column><mj-text mj-class="big" color="#ff0000">t</mj-text></mj-column></mj-section></mj-body></mjml>',
            new RenderOptions(validationLevel: ValidationLevel::Strict)
        );

        $this->assertSame([], $result->errors);
    }

    public function testSkipModeDoesNotValidate(): void
    {
        $result = $this->renderer->render(self::INVALID_COLOR, new RenderOptions(validationLevel: ValidationLevel::Skip));

        $this->assertSame([], $result->errors);
    }

    #[DataProvider('headAttributesProvider')]
    public function testStrictModeValidatesMjAttributesDefaults(string $attributes, string $expectedError): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($expectedError);

        $this->renderer->render(
            "<mjml><mj-head><mj-attributes>{$attributes}</mj-attributes></mj-head><mj-body></mj-body></mjml>",
            new RenderOptions(validationLevel: ValidationLevel::Strict)
        );
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function headAttributesProvider(): array
    {
        return [
            'mj-all' => ['<mj-all padding="1px;x:y" />', 'mj-all: The option "padding"'],
            'mj-class' => ['<mj-class name="c" color="x;y" />', 'mj-class: The option "color"'],
            'mj-class component defaults' => ['<mj-class name="c"><mj-text color="x;y" /></mj-class>', 'mj-text: The option "color"'],
            'component defaults' => ['<mj-button background-color="x;y" />', 'mj-button: The option "background-color"'],
        ];
    }

    public function testSharedAttributeIsValidWhenAnyComponentAcceptsIt(): void
    {
        // "width" is unit(px) on mj-image but unit(px,%) on mj-column: 50% is valid for at least one
        $result = $this->renderer->render(
            '<mjml><mj-head><mj-attributes><mj-all width="50%" /></mj-attributes></mj-head><mj-body></mj-body></mjml>',
            new RenderOptions(validationLevel: ValidationLevel::Strict)
        );

        $this->assertSame([], $result->errors);
    }

    public function testUntrustedInputOptionsAreStrict(): void
    {
        $options = RenderOptions::forUntrustedInput();

        $this->assertSame(ValidationLevel::Strict, $options->validationLevel);
        $this->assertFalse($options->keepComments);
        $this->assertNotNull($options->urlValidator);
    }
}
