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
     * @param array<string, string>               $fonts                     Font URLs indexed by name
     * @param array<string, array<string, mixed>> $headAttributes            Head element attributes
     * @param array<string, string|null>          $inheritedAttributes       Attributes inherited from parent component
     * @param string|null                         $gap                       Gap value for spacing between sections in a wrapper
     * @param string|null                         $navbarBaseUrl             Base URL for navbar links
     * @param string|null                         $accordionFontFamily       Font family inherited from accordion
     * @param string|null                         $elementFontFamily         Font family inherited from accordion element
     * @param string|null                         $accordionBorder           Border inherited from accordion
     * @param string|null                         $accordionIconAlign        Icon alignment inherited from accordion
     * @param string|null                         $accordionIconWidth        Icon width inherited from accordion
     * @param string|null                         $accordionIconHeight       Icon height inherited from accordion
     * @param string|null                         $accordionIconPosition     Icon position inherited from accordion
     * @param string|null                         $accordionIconWrappedUrl   Icon URL for wrapped state
     * @param string|null                         $accordionIconWrappedAlt   Icon alt for wrapped state
     * @param string|null                         $accordionIconUnwrappedUrl Icon URL for unwrapped state
     * @param string|null                         $accordionIconUnwrappedAlt Icon alt for unwrapped state
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
        public ?string $gap = null,
        public ?string $navbarBaseUrl = null,
        public ?string $accordionFontFamily = null,
        public ?string $elementFontFamily = null,
        public ?string $accordionBorder = null,
        public ?string $accordionIconAlign = null,
        public ?string $accordionIconWidth = null,
        public ?string $accordionIconHeight = null,
        public ?string $accordionIconPosition = null,
        public ?string $accordionIconWrappedUrl = null,
        public ?string $accordionIconWrappedAlt = null,
        public ?string $accordionIconUnwrappedUrl = null,
        public ?string $accordionIconUnwrappedAlt = null,
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
     *     globalData: GlobalData,
     *     gap: string|null,
     *     navbarBaseUrl: string|null,
     *     accordionFontFamily: string|null,
     *     elementFontFamily: string|null,
     *     accordionBorder: string|null,
     *     accordionIconAlign: string|null,
     *     accordionIconWidth: string|null,
     *     accordionIconHeight: string|null,
     *     accordionIconPosition: string|null,
     *     accordionIconWrappedUrl: string|null,
     *     accordionIconWrappedAlt: string|null,
     *     accordionIconUnwrappedUrl: string|null,
     *     accordionIconUnwrappedAlt: string|null
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
            'gap' => $this->gap,
            'navbarBaseUrl' => $this->navbarBaseUrl,
            'accordionFontFamily' => $this->accordionFontFamily,
            'elementFontFamily' => $this->elementFontFamily,
            'accordionBorder' => $this->accordionBorder,
            'accordionIconAlign' => $this->accordionIconAlign,
            'accordionIconWidth' => $this->accordionIconWidth,
            'accordionIconHeight' => $this->accordionIconHeight,
            'accordionIconPosition' => $this->accordionIconPosition,
            'accordionIconWrappedUrl' => $this->accordionIconWrappedUrl,
            'accordionIconWrappedAlt' => $this->accordionIconWrappedAlt,
            'accordionIconUnwrappedUrl' => $this->accordionIconUnwrappedUrl,
            'accordionIconUnwrappedAlt' => $this->accordionIconUnwrappedAlt,
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
            gap: $data['gap'] ?? null,
            navbarBaseUrl: $data['navbarBaseUrl'] ?? null,
            accordionFontFamily: $data['accordionFontFamily'] ?? null,
            elementFontFamily: $data['elementFontFamily'] ?? null,
            accordionBorder: $data['accordionBorder'] ?? null,
            accordionIconAlign: $data['accordionIconAlign'] ?? null,
            accordionIconWidth: $data['accordionIconWidth'] ?? null,
            accordionIconHeight: $data['accordionIconHeight'] ?? null,
            accordionIconPosition: $data['accordionIconPosition'] ?? null,
            accordionIconWrappedUrl: $data['accordionIconWrappedUrl'] ?? null,
            accordionIconWrappedAlt: $data['accordionIconWrappedAlt'] ?? null,
            accordionIconUnwrappedUrl: $data['accordionIconUnwrappedUrl'] ?? null,
            accordionIconUnwrappedAlt: $data['accordionIconUnwrappedAlt'] ?? null,
        );
    }
}
