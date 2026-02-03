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

namespace PhpMjml\Tests\Templates;

use PhpMjml\Component\Registry;
use PhpMjml\Parser\MjmlParser;
use PhpMjml\Preset\CorePreset;
use PhpMjml\Renderer\Mjml2Html;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for real-world MJML templates.
 *
 * These tests verify that complex templates render without errors.
 * Unlike parity tests, they don't compare output with the JS implementation.
 */
final class TemplatesTest extends TestCase
{
    private Mjml2Html $renderer;

    protected function setUp(): void
    {
        $registry = new Registry();
        $registry->registerMany(CorePreset::getComponents());

        $this->renderer = new Mjml2Html($registry, new MjmlParser(registry: $registry));
    }

    public static function templateProvider(): iterable
    {
        $templatesDir = __DIR__;

        $files = glob($templatesDir.'/*.mjml');

        if (false === $files) {
            throw new \RuntimeException(\sprintf('Failed to glob templates directory: %s', $templatesDir));
        }

        foreach ($files as $file) {
            $name = basename($file, '.mjml');
            yield $name => [$file, $name];
        }
    }

    #[DataProvider('templateProvider')]
    public function testTemplateRendersSuccessfully(string $filePath, string $templateName): void
    {
        $mjml = file_get_contents($filePath);
        $this->assertNotFalse($mjml, "Failed to read template: {$templateName}");

        $result = $this->renderer->render($mjml);

        $this->assertNotEmpty($result->html, "Template {$templateName} produced empty HTML");
        $this->assertStringContainsString('<!doctype html>', $result->html, "Template {$templateName} missing doctype");
        $this->assertStringContainsString('</html>', $result->html, "Template {$templateName} missing closing html tag");
    }

    public function testBlackFridayTemplateContainsExpectedContent(): void
    {
        $mjml = file_get_contents(__DIR__.'/black-friday.mjml');
        $this->assertNotFalse($mjml);

        $result = $this->renderer->render($mjml);

        // Verify key content is preserved (including &nbsp; entities)
        $this->assertStringContainsString('Black Friday', $result->html);
        $this->assertStringContainsString('WOMEN', $result->html);
        $this->assertStringContainsString('Shop Now', $result->html);
        $this->assertStringContainsString('SALEONSALE', $result->html);

        // Verify background colors are applied
        $this->assertStringContainsString('#F4F4F4', $result->html);
        $this->assertStringContainsString('#000000', $result->html);
    }
}
