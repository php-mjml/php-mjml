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
    public GlobalData $globalData;

    /**
     * @param array<string, string>               $fonts               Font URLs indexed by name
     * @param array<string, array<string, mixed>> $headAttributes      Head element attributes
     * @param array<string, string|null>          $inheritedAttributes Attributes inherited from parent component
     */
    public function __construct(
        public readonly Registry $registry,
        public readonly RenderOptions $options,
        public string $title = '',
        public string $preview = '',
        public array $fonts = [],
        public array $headAttributes = [],
        public int $containerWidth = 600,
        public string $breakpoint = '480px',
        public ?string $backgroundColor = null,
        public string $lang = 'und',
        public string $dir = 'auto',
        public array $inheritedAttributes = [],
        ?GlobalData $globalData = null,
    ) {
        $this->globalData = $globalData ?? new GlobalData();
    }

    /**
     * Get the styles array from global data.
     *
     * @return array<int, string>
     */
    public function getStyles(): array
    {
        return $this->globalData->styles;
    }

    /**
     * Get the media queries array from global data.
     *
     * @return array<string, string>
     */
    public function getMediaQueries(): array
    {
        return $this->globalData->mediaQueries;
    }

    /**
     * Get the component head styles array from global data.
     *
     * @return array<int, string>
     */
    public function getComponentHeadStyles(): array
    {
        return $this->globalData->componentHeadStyle;
    }

    /**
     * Add a media query for responsive column widths.
     *
     * @param string                                      $className CSS class name (e.g., 'mj-column-per-50')
     * @param array{parsedWidth: float|int, unit: string} $data      Width data
     */
    public function addMediaQuery(string $className, array $data): void
    {
        $this->globalData->addMediaQuery($className, $data);
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
     *     headAttributes: array<string, array<string, mixed>>,
     *     containerWidth: int,
     *     breakpoint: string,
     *     backgroundColor: string|null,
     *     lang: string,
     *     dir: string,
     *     inheritedAttributes: array<string, string|null>,
     *     globalData: GlobalData
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
            'headAttributes' => $this->headAttributes,
            'containerWidth' => $this->containerWidth,
            'breakpoint' => $this->breakpoint,
            'backgroundColor' => $this->backgroundColor,
            'lang' => $this->lang,
            'dir' => $this->dir,
            'inheritedAttributes' => $this->inheritedAttributes,
            'globalData' => $this->globalData,
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
            headAttributes: $data['headAttributes'] ?? $base->headAttributes,
            containerWidth: $data['containerWidth'] ?? $base->containerWidth,
            breakpoint: $data['breakpoint'] ?? $base->breakpoint,
            backgroundColor: $data['backgroundColor'] ?? $base->backgroundColor,
            lang: $data['lang'] ?? $base->lang,
            dir: $data['dir'] ?? $base->dir,
            inheritedAttributes: $data['inheritedAttributes'] ?? [],
            globalData: $data['globalData'] ?? $base->globalData,
        );
    }
}
