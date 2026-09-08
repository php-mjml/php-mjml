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

use PhpMjml\Component\BodyComponent;
use PhpMjml\Component\Registry;

/**
 * Context object for MJML rendering, holding all state needed during render.
 *
 * @phpstan-type ClassAttributes array<string, string|null|array<string, array<string, string|null>>>
 * @phpstan-type HeadAttributes array<string, array<string, string|null|ClassAttributes>>
 * @phpstan-type ContextData array{
 *     registry?: Registry,
 *     renderOptions?: RenderOptions,
 *     nonRawSiblings?: int,
 *     title?: string,
 *     preview?: string,
 *     fonts?: array<string, string>,
 *     headAttributes?: HeadAttributes,
 *     containerWidth?: int,
 *     breakpoint?: string,
 *     backgroundColor?: string|null,
 *     lang?: string,
 *     dir?: string,
 *     inheritedAttributes?: array<string, string|null>,
 *     globalData?: GlobalData|null,
 *     componentData?: array<string, array<string, string|null>>,
 * }
 *
 * @property array<string, string|null>|null $gap @deprecated Use getComponentData('gap')
 * @property-read array<string, string|null>|null $navbarBaseUrl     @deprecated Use getComponentData('navbarBaseUrl')
 * @property-read array<string, string|null>|null $accordionSettings @deprecated Use getComponentData('accordion')
 */
final class RenderContext
{
    public string $title;

    public string $preview;

    /** @var array<string, string> */
    public array $fonts;

    /** @var HeadAttributes */
    public array $headAttributes;

    public int $containerWidth;

    public string $breakpoint;

    public ?string $backgroundColor;

    public string $lang;

    public string $dir;

    /** @var array<string, string|null> */
    public array $inheritedAttributes;

    public GlobalData $globalData;

    /** @var array<string, array<string, string|null>> */
    private array $componentData;

    /**
     * @param ContextData $options Context options
     */
    public function __construct(
        public readonly Registry $registry,
        public readonly RenderOptions $renderOptions,
        array $options = [],
    ) {
        $this->title = $options['title'] ?? '';
        $this->preview = $options['preview'] ?? '';
        $this->fonts = $options['fonts'] ?? [];
        $this->headAttributes = $options['headAttributes'] ?? [];
        $this->containerWidth = $options['containerWidth'] ?? BodyComponent::DEFAULT_CONTAINER_WIDTH;
        $this->breakpoint = $options['breakpoint'] ?? '480px';
        $this->backgroundColor = $options['backgroundColor'] ?? null;
        $this->lang = $options['lang'] ?? 'und';
        $this->dir = $options['dir'] ?? 'auto';
        $this->inheritedAttributes = $options['inheritedAttributes'] ?? [];
        $this->globalData = $options['globalData'] ?? new GlobalData();
        $this->componentData = $options['componentData'] ?? [];
    }

    // ===== Backward Compatibility Properties =====

    /**
     * @deprecated Use getComponentData() instead
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            // Legacy component-specific properties (now in componentData)
            'gap' => $this->getComponentData('gap'),
            'navbarBaseUrl' => $this->getComponentData('navbarBaseUrl'),
            'accordionSettings' => $this->getComponentData('accordion'),
            default => throw new \InvalidArgumentException(\sprintf('Unknown property "%s"', $name)),
        };
    }

    public function __set(string $name, mixed $value): void
    {
        throw new \InvalidArgumentException(\sprintf('Unknown property "%s"', $name));
    }

    // ===== Property Accessors =====

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getPreview(): string
    {
        return $this->preview;
    }

    public function setPreview(string $preview): void
    {
        $this->preview = $preview;
    }

    /**
     * @return array<string, string>
     */
    public function getFonts(): array
    {
        return $this->fonts;
    }

    /**
     * @param array<string, string> $fonts
     */
    public function setFonts(array $fonts): void
    {
        $this->fonts = $fonts;
    }

    /**
     * @return HeadAttributes
     */
    public function getHeadAttributes(): array
    {
        return $this->headAttributes;
    }

    /**
     * @param HeadAttributes $headAttributes
     */
    public function setHeadAttributes(array $headAttributes): void
    {
        $this->headAttributes = $headAttributes;
    }

    /**
     * Get renderable default attributes for a component, excluding named class metadata.
     *
     * @return array<string, string|null>
     */
    public function getDefaultAttributes(string $componentName): array
    {
        if ('mj-class' === $componentName) {
            return [];
        }

        return array_filter(
            $this->headAttributes[$componentName] ?? [],
            static fn ($value) => null === $value || \is_string($value)
        );
    }

    public function getContainerWidth(): int
    {
        return $this->containerWidth;
    }

    public function getBreakpoint(): string
    {
        return $this->breakpoint;
    }

    public function setBreakpoint(string $breakpoint): void
    {
        $this->breakpoint = $breakpoint;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $color): void
    {
        $this->backgroundColor = $color;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getDir(): string
    {
        return $this->dir;
    }

    public function setDir(string $dir): void
    {
        $this->dir = $dir;
    }

    // ===== Component Data Accessors =====

    /**
     * Get component-specific data from the context.
     *
     * @param array<string, string|null>|null $default
     *
     * @return array<string, string|null>|null
     */
    public function getComponentData(string $key, ?array $default = null): ?array
    {
        return $this->componentData[$key] ?? $default;
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

    /**
     * Convert context to array for child context propagation.
     *
     * @return ContextData
     */
    public function toArray(): array
    {
        return [
            'registry' => $this->registry,
            'renderOptions' => $this->renderOptions,
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
            'componentData' => $this->componentData,
        ];
    }

    /**
     * Create a new context from array data.
     *
     * @param ContextData $data Array of context data
     */
    public static function fromArray(array $data, self $base): self
    {
        return new self(
            registry: $data['registry'] ?? $base->registry,
            renderOptions: $data['renderOptions'] ?? $base->renderOptions,
            options: [
                'title' => $data['title'] ?? $base->title,
                'preview' => $data['preview'] ?? $base->preview,
                'fonts' => $data['fonts'] ?? $base->fonts,
                'headAttributes' => $data['headAttributes'] ?? $base->headAttributes,
                'containerWidth' => $data['containerWidth'] ?? $base->containerWidth,
                'breakpoint' => $data['breakpoint'] ?? $base->breakpoint,
                'backgroundColor' => $data['backgroundColor'] ?? $base->backgroundColor,
                'lang' => $data['lang'] ?? $base->lang,
                'dir' => $data['dir'] ?? $base->dir,
                'inheritedAttributes' => $data['inheritedAttributes'] ?? [],
                'globalData' => $data['globalData'] ?? $base->globalData,
                'componentData' => $data['componentData'] ?? $base->componentData,
            ],
        );
    }
}
