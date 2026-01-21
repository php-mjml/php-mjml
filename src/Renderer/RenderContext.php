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

namespace PhpMjml\Renderer;

use PhpMjml\Component\Registry;

final class RenderContext
{
    /**
     * @param array<string, string>               $fonts          Font URLs indexed by name
     * @param array<int, string>                  $styles         CSS style strings
     * @param array<string, array<string, mixed>> $headAttributes Head element attributes
     * @param array<string, string>               $mediaQueries   Media query CSS indexed by class name
     */
    public function __construct(
        public readonly Registry $registry,
        public readonly RenderOptions $options,
        public string $title = '',
        public string $preview = '',
        public array $fonts = [],
        public array $styles = [],
        public array $headAttributes = [],
        public int $containerWidth = 600,
        public string $breakpoint = '480px',
        public array $mediaQueries = [],
        public ?string $backgroundColor = null,
        public ?string $lang = null,
        public ?string $dir = null,
    ) {
    }

    /**
     * Add a media query for responsive column widths.
     *
     * @param string                                      $className CSS class name (e.g., 'mj-column-per-50')
     * @param array{parsedWidth: float|int, unit: string} $data      Width data
     */
    public function addMediaQuery(string $className, array $data): void
    {
        if (isset($this->mediaQueries[$className])) {
            return;
        }

        $parsedWidth = $data['parsedWidth'];
        $unit = $data['unit'];

        $cssValue = match ($unit) {
            '%' => "{ width:{$parsedWidth}% !important; max-width: {$parsedWidth}%; }",
            'px' => "{ width:{$parsedWidth}px !important; max-width: {$parsedWidth}px; }",
            default => "{ width:{$parsedWidth}{$unit} !important; max-width: {$parsedWidth}{$unit}; }",
        };

        $this->mediaQueries[$className] = $cssValue;
    }

    /**
     * Set the body background color.
     */
    public function setBackgroundColor(?string $color): void
    {
        $this->backgroundColor = $color;
    }

    /**
     * Create a new context with updated container width.
     */
    public function withContainerWidth(int $width): self
    {
        $new = clone $this;
        $new->containerWidth = $width;

        return $new;
    }

    /**
     * Convert context to array for child context propagation.
     *
     * @return array{
     *     registry: Registry,
     *     options: RenderOptions,
     *     title: string,
     *     preview: string,
     *     fonts: array<string, string>,
     *     styles: array<int, string>,
     *     headAttributes: array<string, array<string, mixed>>,
     *     containerWidth: int,
     *     breakpoint: string,
     *     mediaQueries: array<string, string>,
     *     backgroundColor: string|null,
     *     lang: string|null,
     *     dir: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'registry' => $this->registry,
            'options' => $this->options,
            'title' => $this->title,
            'preview' => $this->preview,
            'fonts' => $this->fonts,
            'styles' => $this->styles,
            'headAttributes' => $this->headAttributes,
            'containerWidth' => $this->containerWidth,
            'breakpoint' => $this->breakpoint,
            'mediaQueries' => $this->mediaQueries,
            'backgroundColor' => $this->backgroundColor,
            'lang' => $this->lang,
            'dir' => $this->dir,
        ];
    }

    /**
     * Create a new context from array data.
     *
     * @param array<string, mixed> $data Array of context data
     */
    public static function fromArray(array $data, self $base): self
    {
        return new self(
            registry: $data['registry'] ?? $base->registry,
            options: $data['options'] ?? $base->options,
            title: $data['title'] ?? $base->title,
            preview: $data['preview'] ?? $base->preview,
            fonts: $data['fonts'] ?? $base->fonts,
            styles: $data['styles'] ?? $base->styles,
            headAttributes: $data['headAttributes'] ?? $base->headAttributes,
            containerWidth: $data['containerWidth'] ?? $base->containerWidth,
            breakpoint: $data['breakpoint'] ?? $base->breakpoint,
            mediaQueries: $data['mediaQueries'] ?? $base->mediaQueries,
            backgroundColor: $data['backgroundColor'] ?? $base->backgroundColor,
            lang: $data['lang'] ?? $base->lang,
            dir: $data['dir'] ?? $base->dir,
        );
    }
}
