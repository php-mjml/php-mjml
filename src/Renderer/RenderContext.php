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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Context object for MJML rendering, holding all state needed during render.
 *
 * @property string                              $title
 * @property string                              $preview
 * @property array<string, string>               $fonts
 * @property array<string, array<string, mixed>> $headAttributes
 * @property int                                 $containerWidth
 * @property string                              $breakpoint
 * @property string|null                         $backgroundColor
 * @property string                              $lang
 * @property string                              $dir
 * @property array<string, string|null>          $inheritedAttributes
 * @property string|null                         $gap                 @deprecated Use getComponentData('gap')
 * @property string|null                         $navbarBaseUrl       @deprecated Use getComponentData('navbarBaseUrl')
 * @property array<string, string|null>|null     $accordionSettings   @deprecated Use getComponentData('accordion')
 */
final class RenderContext
{
    public GlobalData $globalData;

    /** @var array<string, mixed> */
    private array $options;

    /**
     * @param array<string, mixed> $options Context options
     */
    public function __construct(
        public readonly Registry $registry,
        public readonly RenderOptions $renderOptions,
        array $options = [],
    ) {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
        $this->globalData = $this->options['globalData'] ?? new GlobalData();
    }

    // ===== Backward Compatibility Properties =====

    /**
     * @deprecated Use getTitle() instead
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'title' => $this->options['title'],
            'preview' => $this->options['preview'],
            'fonts' => $this->options['fonts'],
            'headAttributes' => $this->options['headAttributes'],
            'containerWidth' => $this->options['containerWidth'],
            'breakpoint' => $this->options['breakpoint'],
            'backgroundColor' => $this->options['backgroundColor'],
            'lang' => $this->options['lang'],
            'dir' => $this->options['dir'],
            'inheritedAttributes' => $this->options['inheritedAttributes'],
            // Legacy component-specific properties (now in componentData)
            'gap' => $this->getComponentData('gap'),
            'navbarBaseUrl' => $this->getComponentData('navbarBaseUrl'),
            'accordionSettings' => $this->getComponentData('accordion'),
            default => throw new \InvalidArgumentException(\sprintf('Unknown property "%s"', $name)),
        };
    }

    /**
     * @deprecated Use setters instead
     */
    public function __set(string $name, mixed $value): void
    {
        match ($name) {
            'title' => $this->options['title'] = $value,
            'preview' => $this->options['preview'] = $value,
            'fonts' => $this->options['fonts'] = $value,
            'headAttributes' => $this->options['headAttributes'] = $value,
            'containerWidth' => $this->options['containerWidth'] = $value,
            'breakpoint' => $this->options['breakpoint'] = $value,
            'backgroundColor' => $this->options['backgroundColor'] = $value,
            'lang' => $this->options['lang'] = $value,
            'dir' => $this->options['dir'] = $value,
            'inheritedAttributes' => $this->options['inheritedAttributes'] = $value,
            default => throw new \InvalidArgumentException(\sprintf('Unknown property "%s"', $name)),
        };
    }

    // ===== Property Accessors =====

    public function getTitle(): string
    {
        return $this->options['title'];
    }

    public function setTitle(string $title): void
    {
        $this->options['title'] = $title;
    }

    public function getPreview(): string
    {
        return $this->options['preview'];
    }

    public function setPreview(string $preview): void
    {
        $this->options['preview'] = $preview;
    }

    /**
     * @return array<string, string>
     */
    public function getFonts(): array
    {
        return $this->options['fonts'];
    }

    /**
     * @param array<string, string> $fonts
     */
    public function setFonts(array $fonts): void
    {
        $this->options['fonts'] = $fonts;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getHeadAttributes(): array
    {
        return $this->options['headAttributes'];
    }

    /**
     * @param array<string, array<string, mixed>> $headAttributes
     */
    public function setHeadAttributes(array $headAttributes): void
    {
        $this->options['headAttributes'] = $headAttributes;
    }

    public function getContainerWidth(): int
    {
        return $this->options['containerWidth'];
    }

    public function getBreakpoint(): string
    {
        return $this->options['breakpoint'];
    }

    public function setBreakpoint(string $breakpoint): void
    {
        $this->options['breakpoint'] = $breakpoint;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->options['backgroundColor'];
    }

    public function setBackgroundColor(?string $color): void
    {
        $this->options['backgroundColor'] = $color;
    }

    public function getLang(): string
    {
        return $this->options['lang'];
    }

    public function setLang(string $lang): void
    {
        $this->options['lang'] = $lang;
    }

    public function getDir(): string
    {
        return $this->options['dir'];
    }

    public function setDir(string $dir): void
    {
        $this->options['dir'] = $dir;
    }

    /**
     * @return array<string, string|null>
     */
    public function getInheritedAttributes(): array
    {
        return $this->options['inheritedAttributes'];
    }

    // ===== Component Data Accessors =====

    /**
     * Get component-specific data from the context.
     */
    public function getComponentData(string $key, mixed $default = null): mixed
    {
        return $this->options['componentData'][$key] ?? $default;
    }

    /**
     * Create a new context with additional component data.
     */
    public function withComponentData(string $key, mixed $value): self
    {
        $new = clone $this;
        $new->options['componentData'][$key] = $value;

        return $new;
    }

    // ===== Global Data Methods =====

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
     * Get the errors array from global data.
     *
     * @return array<int, string>
     */
    public function getErrors(): array
    {
        return $this->globalData->errors;
    }

    // ===== Context Transformation Methods =====

    /**
     * Create a new context with updated container width.
     */
    public function withContainerWidth(int $width): self
    {
        $new = clone $this;
        $new->options['containerWidth'] = $width;

        return $new;
    }

    /**
     * Convert context to array for child context propagation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'registry' => $this->registry,
            'renderOptions' => $this->renderOptions,
            'title' => $this->options['title'],
            'preview' => $this->options['preview'],
            'fonts' => $this->options['fonts'],
            'headAttributes' => $this->options['headAttributes'],
            'containerWidth' => $this->options['containerWidth'],
            'breakpoint' => $this->options['breakpoint'],
            'backgroundColor' => $this->options['backgroundColor'],
            'lang' => $this->options['lang'],
            'dir' => $this->options['dir'],
            'inheritedAttributes' => $this->options['inheritedAttributes'],
            'globalData' => $this->globalData,
            'componentData' => $this->options['componentData'],
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
            renderOptions: $data['renderOptions'] ?? $base->renderOptions,
            options: [
                'title' => $data['title'] ?? $base->options['title'],
                'preview' => $data['preview'] ?? $base->options['preview'],
                'fonts' => $data['fonts'] ?? $base->options['fonts'],
                'headAttributes' => $data['headAttributes'] ?? $base->options['headAttributes'],
                'containerWidth' => $data['containerWidth'] ?? $base->options['containerWidth'],
                'breakpoint' => $data['breakpoint'] ?? $base->options['breakpoint'],
                'backgroundColor' => $data['backgroundColor'] ?? $base->options['backgroundColor'],
                'lang' => $data['lang'] ?? $base->options['lang'],
                'dir' => $data['dir'] ?? $base->options['dir'],
                'inheritedAttributes' => $data['inheritedAttributes'] ?? [],
                'globalData' => $data['globalData'] ?? $base->globalData,
                'componentData' => $data['componentData'] ?? $base->options['componentData'],
            ],
        );
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => '',
            'preview' => '',
            'fonts' => [],
            'headAttributes' => [],
            'containerWidth' => 600,
            'breakpoint' => '480px',
            'backgroundColor' => null,
            'lang' => 'und',
            'dir' => 'auto',
            'inheritedAttributes' => [],
            'globalData' => null,
            'componentData' => [],
        ]);

        $resolver->setAllowedTypes('title', 'string');
        $resolver->setAllowedTypes('preview', 'string');
        $resolver->setAllowedTypes('fonts', 'array');
        $resolver->setAllowedTypes('headAttributes', 'array');
        $resolver->setAllowedTypes('containerWidth', 'int');
        $resolver->setAllowedTypes('breakpoint', 'string');
        $resolver->setAllowedTypes('backgroundColor', ['null', 'string']);
        $resolver->setAllowedTypes('lang', 'string');
        $resolver->setAllowedTypes('dir', 'string');
        $resolver->setAllowedTypes('inheritedAttributes', 'array');
        $resolver->setAllowedTypes('globalData', ['null', GlobalData::class]);
        $resolver->setAllowedTypes('componentData', 'array');
    }
}
