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
 * Component for setting default attributes on other components.
 *
 * The mj-attributes component allows you to define default attributes
 * that will be applied to other components globally. Its children
 * specify which components should receive which default attributes.
 *
 * Example:
 * <mj-attributes>
 *   <mj-all font-family="Arial" />
 *   <mj-text color="red" />
 *   <mj-class name="blue" color="blue" />
 * </mj-attributes>
 */
final class Attributes extends HeadComponent
{
    public const TAG_NAME_ALL = 'mj-all';
    public const TAG_NAME_CLASS = 'mj-class';

    /**
     * Raw child nodes from the parser.
     *
     * @var array<Node>
     */
    private array $rawChildren = [];

    public static function getComponentName(): string
    {
        return 'mj-attributes';
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
        $headAttributes = $context->getHeadAttributes();

        foreach ($this->rawChildren as $child) {
            $tagName = $child->tagName;
            $attributes = $child->attributes;

            if (self::TAG_NAME_CLASS === $tagName) {
                // mj-class defines named CSS classes that can be applied via mj-class attribute
                $className = $attributes['name'] ?? null;
                if (null === $className || '' === $className) {
                    continue;
                }

                // Store class attributes (excluding the 'name' attribute)
                $classAttributes = array_filter(
                    $attributes,
                    static fn (string $key) => 'name' !== $key,
                    \ARRAY_FILTER_USE_KEY
                );
                $headAttributes[self::TAG_NAME_CLASS][$className] = $classAttributes;

                // Process nested children for component-specific class defaults
                foreach ($child->children as $nestedChild) {
                    $nestedTagName = $nestedChild->tagName;
                    $headAttributes[self::TAG_NAME_CLASS][$className]['__defaults'][$nestedTagName] = $nestedChild->attributes;
                }
            } else {
                // For mj-all and other component tags, store attributes directly
                if (!isset($headAttributes[$tagName])) {
                    $headAttributes[$tagName] = [];
                }
                $headAttributes[$tagName] = array_merge(
                    $headAttributes[$tagName],
                    $attributes
                );
            }
        }

        $context->setHeadAttributes($headAttributes);
    }
}
