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
use PhpMjml\Helper\ConditionalTag;

final class Carousel extends BodyComponent
{
    private const DEFAULT_LEFT_ICON = 'https://i.imgur.com/xTh3hln.png';
    private const DEFAULT_RIGHT_ICON = 'https://i.imgur.com/os7o9kz.png';
    private const THUMBNAIL_VISIBLE = 'visible';
    private const THUMBNAIL_SUPPORTED = 'supported';

    protected static bool $endingTag = false;

    private string $carouselId;

    public function __construct(
        array $attributes = [],
        array $children = [],
        string $content = '',
        ?\PhpMjml\Renderer\RenderContext $context = null,
        array $props = [],
    ) {
        parent::__construct($attributes, $children, $content, $context, $props);
        $this->carouselId = $this->generateRandomHexString(16);
    }

    public static function getComponentName(): string
    {
        return 'mj-carousel';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'align' => 'enum(left,center,right)',
            'border-radius' => 'unit(px,%){1,4}',
            'container-background-color' => 'color',
            'icon-width' => 'unit(px,%)',
            'left-icon' => 'string',
            'padding' => 'unit(px,%){1,4}',
            'padding-top' => 'unit(px,%)',
            'padding-bottom' => 'unit(px,%)',
            'padding-left' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'right-icon' => 'string',
            'thumbnails' => 'enum(visible,hidden,supported)',
            'tb-border' => 'string',
            'tb-border-radius' => 'unit(px,%)',
            'tb-hover-border-color' => 'color',
            'tb-selected-border-color' => 'color',
            'tb-width' => 'unit(px,%)',
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'align' => 'center',
            'border-radius' => '6px',
            'icon-width' => '44px',
            'left-icon' => self::DEFAULT_LEFT_ICON,
            'right-icon' => self::DEFAULT_RIGHT_ICON,
            'thumbnails' => self::THUMBNAIL_VISIBLE,
            'tb-border' => '2px solid transparent',
            'tb-border-radius' => '6px',
            'tb-hover-border-color' => '#fead0d',
            'tb-selected-border-color' => '#ccc',
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

    /**
     * @return array<string, mixed>
     */
    public function getChildContext(): array
    {
        $context = parent::getChildContext();
        $context['carouselId'] = $this->carouselId;
        $context['thumbnails'] = $this->getAttribute('thumbnails');
        $context['tb-border'] = $this->getAttribute('tb-border');
        $context['tb-border-radius'] = $this->getAttribute('tb-border-radius');
        $context['tb-width'] = $this->getThumbnailsWidth();
        $context['border-radius'] = $this->getAttribute('border-radius');

        return $context;
    }

    public function render(): string
    {
        $length = \count($this->children);
        if (0 === $length) {
            return '';
        }

        // Add component head style
        $this->addComponentHeadStyle();

        $carouselContent = ConditionalTag::wrapMso(
            \sprintf(
                '<div %s>%s<div %s>%s%s</div></div>',
                $this->htmlAttributes(['class' => 'mj-carousel']),
                $this->generateRadios(),
                $this->htmlAttributes([
                    'class' => "mj-carousel-content mj-carousel-{$this->carouselId}-content",
                    'style' => [
                        'display' => 'table',
                        'width' => '100%',
                        'table-layout' => 'fixed',
                        'text-align' => 'center',
                        'font-size' => '0px',
                    ],
                ]),
                $this->generateThumbnails(),
                $this->generateCarousel(),
            ),
            true
        );

        return $carouselContent.$this->renderFallback();
    }

    private function generateRandomHexString(int $length): string
    {
        $byteLength = max(1, (int) ($length / 2));

        return bin2hex(random_bytes($byteLength));
    }

    private function getThumbnailsWidth(): string
    {
        $length = \count($this->children);
        if (0 === $length) {
            return '0';
        }

        $tbWidth = $this->getAttribute('tb-width');
        if (null !== $tbWidth) {
            return $tbWidth;
        }

        $containerWidth = (null !== $this->context) ? $this->context->containerWidth : 600;
        $width = min($containerWidth / $length, 110);

        return $width.'px';
    }

    private function addComponentHeadStyle(): void
    {
        if (null === $this->context) {
            return;
        }

        $length = \count($this->children);
        if (0 === $length) {
            return;
        }

        $iconWidth = $this->getAttribute('icon-width');
        $tbSelectedBorderColor = $this->getAttribute('tb-selected-border-color');
        $tbHoverBorderColor = $this->getAttribute('tb-hover-border-color');

        // Build the carousel CSS
        $carouselCss = <<<CSS
    .mj-carousel {
      -webkit-user-select: none;
      -moz-user-select: none;
      user-select: none;
    }

    .mj-carousel-{$this->carouselId}-icons-cell {
      display: table-cell !important;
      width: {$iconWidth} !important;
    }

    .mj-carousel-radio,
    .mj-carousel-next,
    .mj-carousel-previous {
      display: none !important;
    }

    .mj-carousel-thumbnail,
    .mj-carousel-next,
    .mj-carousel-previous {
      touch-action: manipulation;
    }

CSS;

        // Hide all images by default (when radio is checked)
        $hideSelectors = [];
        for ($i = 0; $i < $length; ++$i) {
            $hideSelectors[] = ".mj-carousel-{$this->carouselId}-radio:checked "
                .str_repeat('+ * ', $i)
                .'+ .mj-carousel-content .mj-carousel-image';
        }
        $carouselCss .= '    '.implode(",\n    ", $hideSelectors)." {\n      display: none !important;\n    }\n\n";

        // Show the selected image
        $showSelectors = [];
        for ($i = 0; $i < $length; ++$i) {
            $showSelectors[] = ".mj-carousel-{$this->carouselId}-radio-".($i + 1).':checked '
                .str_repeat('+ * ', $length - $i - 1)
                .'+ .mj-carousel-content .mj-carousel-image-'.($i + 1);
        }
        $carouselCss .= '    '.implode(",\n    ", $showSelectors)." {\n      display: block !important;\n    }\n\n";

        // Navigation arrows
        $prevNextSelectors = ['.mj-carousel-previous-icons', '.mj-carousel-next-icons'];
        for ($i = 0; $i < $length; ++$i) {
            $nextIndex = (($i + 1) % $length) + 1;
            $prevIndex = ((($i - 1) % $length) + $length) % $length + 1;

            $prevNextSelectors[] = ".mj-carousel-{$this->carouselId}-radio-".($i + 1).':checked '
                .str_repeat('+ * ', $length - $i - 1)
                ."+ .mj-carousel-content .mj-carousel-next-{$nextIndex}";
            $prevNextSelectors[] = ".mj-carousel-{$this->carouselId}-radio-".($i + 1).':checked '
                .str_repeat('+ * ', $length - $i - 1)
                ."+ .mj-carousel-content .mj-carousel-previous-{$prevIndex}";
        }
        $carouselCss .= '    '.implode(",\n    ", $prevNextSelectors)." {\n      display: block !important;\n    }\n\n";

        // Thumbnail selected border
        $tbSelectedSelectors = [];
        for ($i = 0; $i < $length; ++$i) {
            $tbSelectedSelectors[] = ".mj-carousel-{$this->carouselId}-radio-".($i + 1).':checked '
                .str_repeat('+ * ', $length - $i - 1)
                ."+ .mj-carousel-content .mj-carousel-{$this->carouselId}-thumbnail-".($i + 1);
        }
        $carouselCss .= '    '.implode(",\n    ", $tbSelectedSelectors)." {\n      border-color: {$tbSelectedBorderColor} !important;\n    }\n\n";

        // Thumbnail display when selected
        $tbDisplaySelectors = [];
        for ($i = 0; $i < $length; ++$i) {
            $tbDisplaySelectors[] = ".mj-carousel-{$this->carouselId}-radio-".($i + 1).':checked '
                .str_repeat('+ * ', $length - $i - 1)
                ."+ .mj-carousel-content .mj-carousel-{$this->carouselId}-thumbnail\n          ";
        }
        $carouselCss .= '    '.implode(",\n    ", $tbDisplaySelectors)." {\n      display: inline-block !important;\n    }\n\n";

        // Hide img + div fallbacks
        $carouselCss .= <<<CSS
    .mj-carousel-image img + div,
    .mj-carousel-thumbnail img + div {
      display: none !important;
    }

CSS;

        // Thumbnail hover - hide other images
        $tbHoverHideSelectors = [];
        for ($i = 0; $i < $length; ++$i) {
            $tbHoverHideSelectors[] = ".mj-carousel-{$this->carouselId}-thumbnail:hover "
                .str_repeat('+ * ', $length - $i - 1)
                .'+ .mj-carousel-main .mj-carousel-image';
        }
        $carouselCss .= '    '.implode(",\n    ", $tbHoverHideSelectors)." {\n      display: none !important;\n    }\n\n";

        // Thumbnail hover border
        $carouselCss .= <<<CSS
    .mj-carousel-thumbnail:hover {
      border-color: {$tbHoverBorderColor} !important;
    }

CSS;

        // Thumbnail hover - show hovered image
        $tbHoverShowSelectors = [];
        for ($i = 0; $i < $length; ++$i) {
            $tbHoverShowSelectors[] = ".mj-carousel-{$this->carouselId}-thumbnail-".($i + 1).':hover '
                .str_repeat('+ * ', $length - $i - 1)
                .'+ .mj-carousel-main .mj-carousel-image-'.($i + 1);
        }
        $carouselCss .= '    '.implode(",\n    ", $tbHoverShowSelectors)." {\n      display: block !important;\n    }\n    ";

        // Fallback styles
        $fallbackCss = <<<CSS

      .mj-carousel noinput { display:block !important; }
      .mj-carousel noinput .mj-carousel-image-1 { display: block !important;  }
      .mj-carousel noinput .mj-carousel-arrows,
      .mj-carousel noinput .mj-carousel-thumbnails { display: none !important; }

      [owa] .mj-carousel-thumbnail { display: none !important; }

      @media screen yahoo {
          .mj-carousel-{$this->carouselId}-icons-cell,
          .mj-carousel-previous-icons,
          .mj-carousel-next-icons {
              display: none !important;
          }

          .mj-carousel-{$this->carouselId}-radio-1:checked {$this->generateRepeat('+ *', $length - 1)}+ .mj-carousel-content .mj-carousel-{$this->carouselId}-thumbnail-1 {
              border-color: transparent;
          }
      }
CSS;

        $this->context->globalData->addComponentHeadStyle($carouselCss.$fallbackCss);
    }

    private function generateRepeat(string $str, int $count): string
    {
        if ($count <= 0) {
            return '';
        }

        return str_repeat($str, $count);
    }

    private function generateRadios(): string
    {
        $output = '';

        foreach ($this->children as $index => $child) {
            if ($child instanceof CarouselImage) {
                $output .= $child->renderRadio($this->carouselId, (int) $index);
            }
        }

        return $output;
    }

    private function generateThumbnails(): string
    {
        $thumbnails = $this->getAttribute('thumbnails');
        if (!\in_array($thumbnails, [self::THUMBNAIL_VISIBLE, self::THUMBNAIL_SUPPORTED], true)) {
            return '';
        }

        $output = '';

        foreach ($this->children as $index => $child) {
            if ($child instanceof CarouselImage) {
                $output .= $child->renderThumbnail(
                    $this->carouselId,
                    (int) $index,
                    $this->getAttribute('tb-border'),
                    $this->getAttribute('tb-border-radius'),
                    $this->getThumbnailsWidth(),
                    $thumbnails,
                );
            }
        }

        return $output;
    }

    private function generateControls(string $direction, ?string $icon): string
    {
        $iconWidth = (int) $this->getAttribute('icon-width');
        $length = \count($this->children);

        $labels = '';
        for ($i = 1; $i <= $length; ++$i) {
            $labels .= \sprintf(
                '<label %s><img %s /></label>',
                $this->htmlAttributes([
                    'for' => "mj-carousel-{$this->carouselId}-radio-{$i}",
                    'class' => "mj-carousel-{$direction} mj-carousel-{$direction}-{$i}",
                ]),
                $this->htmlAttributes([
                    'src' => $icon,
                    'alt' => $direction,
                    'style' => [
                        'display' => 'block',
                        'width' => $this->getAttribute('icon-width'),
                        'height' => 'auto',
                    ],
                    'width' => $iconWidth,
                ]),
            );
        }

        return \sprintf(
            '<td %s><div %s>%s</div></td>',
            $this->htmlAttributes([
                'class' => "mj-carousel-{$this->carouselId}-icons-cell",
                'style' => [
                    'font-size' => '0px',
                    'display' => 'none',
                    'mso-hide' => 'all',
                    'padding' => '0px',
                ],
            ]),
            $this->htmlAttributes([
                'class' => "mj-carousel-{$direction}-icons",
                'style' => [
                    'display' => 'none',
                    'mso-hide' => 'all',
                ],
            ]),
            $labels,
        );
    }

    private function generateImages(): string
    {
        $imagesHtml = '';

        foreach ($this->children as $index => $child) {
            if ($child instanceof CarouselImage) {
                $imagesHtml .= $child->renderImage(
                    (int) $index,
                    $this->getAttribute('border-radius'),
                );
            }
        }

        return \sprintf(
            '<td %s><div %s>%s</div></td>',
            $this->htmlAttributes([
                'style' => ['padding' => '0px'],
            ]),
            $this->htmlAttributes(['class' => 'mj-carousel-images']),
            $imagesHtml,
        );
    }

    private function generateCarousel(): string
    {
        return \sprintf(
            '<table %s><tbody><tr>%s%s%s</tr></tbody></table>',
            $this->htmlAttributes([
                'style' => [
                    'caption-side' => 'top',
                    'display' => 'table-caption',
                    'table-layout' => 'fixed',
                    'width' => '100%',
                ],
                'border' => '0',
                'cellpadding' => '0',
                'cellspacing' => '0',
                'width' => '100%',
                'role' => 'presentation',
                'class' => 'mj-carousel-main',
            ]),
            $this->generateControls('previous', $this->getAttribute('left-icon')),
            $this->generateImages(),
            $this->generateControls('next', $this->getAttribute('right-icon')),
        );
    }

    private function renderFallback(): string
    {
        if ([] === $this->children) {
            return '';
        }

        $firstChild = $this->children[0];
        if (!$firstChild instanceof CarouselImage) {
            return '';
        }

        $fallbackImage = $firstChild->renderImage(0, $this->getAttribute('border-radius'));

        return ConditionalTag::wrapMso($fallbackImage);
    }
}
