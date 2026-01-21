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

namespace PhpMjml\Tests\Parity;

use PhpMjml\Component\Registry;
use PhpMjml\Parser\MjmlParser;
use PhpMjml\Preset\CorePreset;
use PhpMjml\Renderer\Mjml2Html;
use PHPUnit\Framework\TestCase;

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
        if (!$this->isMjmlCliAvailable()) {
            $this->markTestSkipped('MJML CLI (npx mjml) is not available');
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'mjml_');
        file_put_contents($tempFile, $mjml);

        $command = \sprintf(
            'npx mjml %s --config.minify false --config.beautify false 2>&1',
            escapeshellarg($tempFile)
        );

        $output = shell_exec($command);
        unlink($tempFile);

        if (null === $output || false === $output) {
            throw new \RuntimeException('Failed to execute mjml CLI');
        }

        // Check if output looks like an error (not HTML)
        if (!str_contains($output, '<!doctype html>') && !str_contains($output, '<!DOCTYPE html>')) {
            throw new \RuntimeException('MJML CLI returned an error: '.$output);
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
        $original = $html;

        // Remove FILE comments added by MJML CLI
        $html = preg_replace('/<!-- FILE: [^>]+ -->/', '', $html) ?? $html;

        // Remove whitespace between tags
        $html = preg_replace('/>\s+</', '><', $html) ?? $html;

        // Normalize whitespace inside style tags
        $html = preg_replace_callback('/<style[^>]*>(.*?)<\/style>/s', function ($matches) {
            $content = $matches[0];
            // Remove leading whitespace from each line inside style
            $content = preg_replace('/^\s+/m', '', $content);

            return $content ?? $matches[0];
        }, $html) ?? $html;

        // Normalize spaces before > in tags (remove trailing spaces in attributes)
        $html = preg_replace('/\s+>/', '>', $html) ?? $html;

        // Normalize empty style attributes: style="" -> style (for HTML5 compatibility)
        $html = preg_replace('/\bstyle=""/', 'style', $html) ?? $html;

        // Normalize multiple spaces to single space
        $html = preg_replace('/\s+/', ' ', $html) ?? $html;

        // Normalize attribute order by parsing and re-serializing
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        // Suppress warnings for HTML5 elements
        libxml_use_internal_errors(true);
        $dom->loadHTML($html, \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $result = $dom->saveHTML();

        return false !== $result ? $result : $original;
    }

    protected function getFixturePath(string $name): string
    {
        return __DIR__.'/fixtures/'.$name.'.mjml';
    }

    protected function loadFixture(string $name): string
    {
        $path = $this->getFixturePath($name);

        if (!file_exists($path)) {
            throw new \RuntimeException(\sprintf('Fixture not found: %s', $path));
        }

        $content = file_get_contents($path);

        if (false === $content) {
            throw new \RuntimeException(\sprintf('Failed to read fixture file: %s', $path));
        }

        return $content;
    }

    private function isMjmlCliAvailable(): bool
    {
        $output = shell_exec('npx mjml --version 2>&1');

        return \is_string($output) && str_contains($output, 'mjml-core:');
    }
}
