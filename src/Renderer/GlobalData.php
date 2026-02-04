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

/**
 * Shared global data that persists across context clones.
 *
 * This class holds data that needs to be accumulated during rendering
 * and accessed from the root context, such as media queries and styles.
 */
final class GlobalData
{
    /**
     * @param array<string, string>                     $mediaQueries       Media query CSS indexed by class name
     * @param array<int, string>                        $styles             CSS style strings
     * @param array<int, string>                        $componentHeadStyle Component head styles (output after media queries)
     * @param array<string, string>                     $headStyle          Keyed head styles (deduplicated by component name)
     * @param list<string>                              $inlineStyles       Inline CSS styles for inlining into elements
     * @param array<string, array<string, string|null>> $htmlAttributes     Custom HTML attributes indexed by CSS selector
     * @param array<int, string>                        $errors             Validation errors collected during rendering
     */
    public function __construct(
        public array $mediaQueries = [],
        public array $styles = [],
        public array $componentHeadStyle = [],
        public array $headStyle = [],
        public array $inlineStyles = [],
        public array $htmlAttributes = [],
        public array $errors = [],
    ) {
    }

    /**
     * Add a validation error message.
     */
    public function addError(string $error): void
    {
        $this->errors[] = $error;
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
     * Add a style string.
     */
    public function addStyle(string $style): void
    {
        $this->styles[] = $style;
    }

    /**
     * Add a component head style string (output after media queries).
     */
    public function addComponentHeadStyle(string $style): void
    {
        $this->componentHeadStyle[] = $style;
    }

    /**
     * Add a keyed head style (deduplicated by key).
     *
     * Use this for component styles that should only be output once
     * regardless of how many instances of the component exist.
     */
    public function addHeadStyle(string $key, string $style): void
    {
        $this->headStyle[$key] = $style;
    }

    /**
     * Add custom HTML attributes for a CSS selector.
     *
     * @param string                     $selector   CSS selector (e.g., ".custom div")
     * @param array<string, string|null> $attributes Attribute name/value pairs
     */
    public function addHtmlAttributes(string $selector, array $attributes): void
    {
        if (!isset($this->htmlAttributes[$selector])) {
            $this->htmlAttributes[$selector] = [];
        }

        $this->htmlAttributes[$selector] = array_merge(
            $this->htmlAttributes[$selector],
            $attributes
        );
    }
}
