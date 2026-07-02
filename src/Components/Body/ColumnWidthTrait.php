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

namespace PhpMjml\Components\Body;

use PhpMjml\Helper\WidthParser;

/**
 * Shared width calculation for column-like components (mj-column, mj-group).
 *
 * The width attribute defaults to an equal share among non-raw siblings.
 * The generated responsive class (mj-column-per-* or mj-column-px-*) is
 * registered as a media query on the render context.
 */
trait ColumnWidthTrait
{
    /**
     * @return array{parsedWidth: float|int, unit: string}
     */
    private function getParsedWidth(): array
    {
        $nonRawSiblings = $this->props['nonRawSiblings'] ?? 1;

        $width = $this->getAttribute('width') ?? \sprintf('%d%%', (int) (100 / $nonRawSiblings));

        return WidthParser::parse($width, parseFloatToInt: false);
    }

    private function getWidthAsPixel(): string
    {
        $containerWidth = $this->getContainerWidth();
        $parsed = $this->getParsedWidth();

        if ('%' === $parsed['unit']) {
            return \sprintf('%dpx', (int) (($containerWidth * $parsed['parsedWidth']) / 100));
        }

        return \sprintf('%dpx', (int) $parsed['parsedWidth']);
    }

    private function getColumnClass(): string
    {
        $parsed = $this->getParsedWidth();
        $parsedWidth = $parsed['parsedWidth'];
        $unit = $parsed['unit'];

        $formattedClassNb = str_replace('.', '-', (string) $parsedWidth);

        $className = match ($unit) {
            '%' => "mj-column-per-{$formattedClassNb}",
            default => "mj-column-px-{$formattedClassNb}",
        };

        // Register media query
        $this->context?->addMediaQuery($className, [
            'parsedWidth' => $parsedWidth,
            'unit' => $unit,
        ]);

        return $className;
    }
}
