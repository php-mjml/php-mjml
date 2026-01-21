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

use PHPUnit\Framework\Attributes\DataProvider;

final class CoreComponentsParityTest extends ParityTestCase
{
    public static function fixtureProvider(): iterable
    {
        $fixturesDir = __DIR__.'/fixtures';

        if (!is_dir($fixturesDir)) {
            return;
        }

        $files = glob($fixturesDir.'/*.mjml');

        if (false === $files) {
            throw new \RuntimeException(\sprintf('Failed to glob fixtures directory: %s', $fixturesDir));
        }

        foreach ($files as $file) {
            $name = basename($file, '.mjml');
            yield $name => [$name];
        }
    }

    #[DataProvider('fixtureProvider')]
    public function testParityWithJavascript(string $fixtureName): void
    {
        $mjml = $this->loadFixture($fixtureName);

        $jsHtml = $this->renderWithJs($mjml);
        $phpHtml = $this->renderWithPhp($mjml);

        $this->assertHtmlEquals($jsHtml, $phpHtml, "Parity failed for fixture: {$fixtureName}");
    }
}
