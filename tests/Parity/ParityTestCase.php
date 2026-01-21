<?php

declare(strict_types=1);

namespace PhpMjml\Tests\Parity;

use PHPUnit\Framework\TestCase;
use PhpMjml\Renderer\Mjml2Html;
use PhpMjml\Parser\MjmlParser;
use PhpMjml\Component\Registry;
use PhpMjml\Preset\CorePreset;
use RuntimeException;

abstract class ParityTestCase extends TestCase
{
    protected Mjml2Html $renderer;

    protected function setUp(): void
    {
        $registry = new Registry();
        $registry->registerMany(CorePreset::getComponents());

        $this->renderer = new Mjml2Html($registry, new MjmlParser());
    }

    protected function renderWithPhp(string $mjml): string
    {
        return $this->renderer->render($mjml)->html;
    }

    protected function renderWithJs(string $mjml): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'mjml_');
        file_put_contents($tempFile, $mjml);

        $command = sprintf(
            'npx mjml %s --config.minify false --config.beautify false 2>&1',
            escapeshellarg($tempFile)
        );

        $output = shell_exec($command);
        unlink($tempFile);

        if ($output === null || $output === false) {
            throw new RuntimeException('Failed to execute mjml CLI');
        }

        return $output;
    }

    protected function assertHtmlEquals(string $expected, string $actual, string $message = ''): void
    {
        $normalizedExpected = $this->normalizeHtml($expected);
        $normalizedActual = $this->normalizeHtml($actual);

        $this->assertEquals($normalizedExpected, $normalizedActual, $message);
    }

    protected function normalizeHtml(string $html): string
    {
        // Remove whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html);

        // Normalize attribute order by parsing and re-serializing
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        // Suppress warnings for HTML5 elements
        libxml_use_internal_errors(true);
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        return $dom->saveHTML() ?: $html;
    }

    protected function getFixturePath(string $name): string
    {
        return __DIR__ . '/fixtures/' . $name . '.mjml';
    }

    protected function loadFixture(string $name): string
    {
        $path = $this->getFixturePath($name);

        if (!file_exists($path)) {
            throw new RuntimeException(sprintf('Fixture not found: %s', $path));
        }

        return file_get_contents($path);
    }
}
