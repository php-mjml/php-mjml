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

use PhpMjml\Component\BodyComponent;

final class CarouselImage extends BodyComponent
{
    protected static bool $endingTag = true;

    public static function getComponentName(): string
    {
        return 'mj-carousel-image';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'alt' => 'string',
            'href' => 'string',
            'rel' => 'string',
            'target' => 'string',
            'title' => 'string',
            'src' => 'string',
            'thumbnails-src' => 'string',
            'border-radius' => 'unit(px,%){1,4}',
            'tb-border' => 'string',
            'tb-border-radius' => 'unit(px,%){1,4}',
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'alt' => '',
            'target' => '_blank',
        ];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        // Styles are inlined directly in render methods, so this returns empty
        return [];
    }

    public function render(): string
    {
        // This render method is called when the component is processed normally
        // The actual rendering is done via renderImage, renderRadio, renderThumbnail
        // called by the parent Carousel component
        return '';
    }

    /**
     * Render the radio input for carousel navigation.
     */
    public function renderRadio(string $carouselId, int $index): string
    {
        return \sprintf(
            '<input %s />',
            $this->htmlAttributes([
                'class' => "mj-carousel-radio mj-carousel-{$carouselId}-radio mj-carousel-{$carouselId}-radio-".($index + 1),
                'checked' => 0 === $index ? 'checked' : null,
                'type' => 'radio',
                'name' => "mj-carousel-radio-{$carouselId}",
                'id' => "mj-carousel-{$carouselId}-radio-".($index + 1),
                'style' => [
                    'display' => 'none',
                    'mso-hide' => 'all',
                ],
            ]),
        );
    }

    /**
     * Render the thumbnail for carousel navigation.
     */
    public function renderThumbnail(
        string $carouselId,
        int $index,
        ?string $tbBorder,
        ?string $tbBorderRadius,
        string $tbWidth,
        ?string $thumbnails,
    ): string {
        $src = $this->getAttribute('src');
        $alt = $this->getAttribute('alt');
        $target = $this->getAttribute('target');
        $thumbnailsSrc = $this->getAttribute('thumbnails-src');
        $imgIndex = $index + 1;

        $cssClass = $this->suffixCssClasses($this->getAttribute('css-class'), 'thumbnail');
        $hasThumbnailsSupported = 'supported' === $thumbnails;

        return \sprintf(
            '<a %s><label %s><img %s /></label></a>',
            $this->htmlAttributes([
                'style' => [
                    'border' => $tbBorder,
                    'border-radius' => $tbBorderRadius,
                    'display' => $hasThumbnailsSupported ? 'none' : 'inline-block',
                    'overflow' => 'hidden',
                    'width' => $tbWidth,
                ],
                'href' => '#'.$imgIndex,
                'target' => $target,
                'class' => "mj-carousel-thumbnail mj-carousel-{$carouselId}-thumbnail mj-carousel-{$carouselId}-thumbnail-{$imgIndex} {$cssClass}",
            ]),
            $this->htmlAttributes([
                'for' => "mj-carousel-{$carouselId}-radio-{$imgIndex}",
            ]),
            $this->htmlAttributes([
                'style' => [
                    'display' => 'block',
                    'width' => '100%',
                    'height' => 'auto',
                ],
                'src' => $thumbnailsSrc ?? $src,
                'alt' => $alt,
                'width' => (int) $tbWidth,
            ]),
        );
    }

    /**
     * Render the main carousel image.
     */
    public function renderImage(int $index, ?string $borderRadius): string
    {
        $src = $this->getAttribute('src');
        $alt = $this->getAttribute('alt');
        $href = $this->getAttribute('href');
        $rel = $this->getAttribute('rel');
        $title = $this->getAttribute('title');
        $containerWidth = (null !== $this->context) ? $this->context->containerWidth : 600;

        $image = \sprintf(
            '<img %s />',
            $this->htmlAttributes([
                'title' => $title,
                'src' => $src,
                'alt' => $alt,
                'style' => [
                    'border-radius' => $borderRadius,
                    'display' => 'block',
                    'width' => $containerWidth.'px',
                    'max-width' => '100%',
                    'height' => 'auto',
                ],
                'width' => $containerWidth,
                'border' => '0',
            ]),
        );

        if (null !== $href) {
            $image = \sprintf(
                '<a %s>%s</a>',
                $this->htmlAttributes([
                    'href' => $href,
                    'rel' => $rel,
                    'target' => '_blank',
                ]),
                $image,
            );
        }

        $cssClass = $this->getAttribute('css-class') ?? '';
        $isFirstImage = 0 === $index;

        $divStyle = $isFirstImage
            ? []
            : ['display' => 'none', 'mso-hide' => 'all'];

        return \sprintf(
            '<div %s>%s</div>',
            $this->htmlAttributes([
                'class' => 'mj-carousel-image mj-carousel-image-'.($index + 1)." {$cssClass}",
                'style' => $divStyle,
            ]),
            $image,
        );
    }

    private function suffixCssClasses(?string $classes, string $suffix): string
    {
        if (null === $classes || '' === $classes) {
            return '';
        }

        $classArray = preg_split('/\s+/', $classes, -1, \PREG_SPLIT_NO_EMPTY);
        if (false === $classArray || [] === $classArray) {
            return '';
        }

        return implode(' ', array_map(
            fn (string $class) => "{$class}-{$suffix}",
            $classArray
        ));
    }
}
