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

namespace PhpMjml\Components\Head;

use PhpMjml\Component\HeadComponent;
use PhpMjml\Parser\Node;
use PhpMjml\Renderer\RenderContext;

/**
 * Component for adding custom HTML attributes to elements using CSS selectors.
 *
 * The mj-html-attributes component allows you to add custom attributes on any
 * HTML tag within the generated HTML, using CSS selectors.
 *
 * Example:
 * <mj-html-attributes>
 *   <mj-selector path=".custom div">
 *     <mj-html-attribute name="data-id">42</mj-html-attribute>
 *   </mj-selector>
 * </mj-html-attributes>
 */
final class HtmlAttributes extends HeadComponent
{
    public const TAG_NAME_SELECTOR = 'mj-selector';
    public const TAG_NAME_ATTRIBUTE = 'mj-html-attribute';

    /**
     * Raw child nodes from the parser.
     *
     * @var array<Node>
     */
    private array $rawChildren = [];

    public static function getComponentName(): string
    {
        return 'mj-html-attributes';
    }

    /**
     * Set the raw child nodes from the parser.
     *
     * @param array<Node> $nodes
     */
    public function setRawChildren(array $nodes): void
    {
        $this->rawChildren = $nodes;
    }

    public function handle(RenderContext $context): void
    {
        foreach ($this->rawChildren as $child) {
            if (self::TAG_NAME_SELECTOR !== $child->tagName) {
                continue;
            }

            $path = $child->attributes['path'] ?? null;
            if (null === $path || '' === $path) {
                continue;
            }

            $attributes = $this->extractAttributes($child);

            if ([] !== $attributes) {
                $context->globalData->addHtmlAttributes($path, $attributes);
            }
        }
    }

    /**
     * Extract HTML attributes from mj-selector children.
     *
     * @return array<string, string|null>
     */
    private function extractAttributes(Node $selectorNode): array
    {
        $attributes = [];

        foreach ($selectorNode->children as $child) {
            if (self::TAG_NAME_ATTRIBUTE !== $child->tagName) {
                continue;
            }

            $name = $child->attributes['name'] ?? null;
            if (null === $name || '' === $name) {
                continue;
            }

            // The content of the mj-html-attribute is the value
            $attributes[$name] = $child->content;
        }

        return $attributes;
    }
}
