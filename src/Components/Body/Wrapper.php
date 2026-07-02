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
use PhpMjml\Component\ComponentInterface;
use PhpMjml\Helper\ConditionalTag;
use PhpMjml\Helper\CssHelper;

/**
 * mj-wrapper is a full-width section container that stacks mj-section children vertically.
 *
 * Unlike mj-section which arranges columns horizontally in a single row,
 * mj-wrapper renders each child section in its own table row. Everything else
 * (backgrounds, borders, full-width handling) is inherited from Section,
 * mirroring the JS implementation where MjWrapper extends MjSection.
 */
final class Wrapper extends Section
{
    public static function getComponentName(): string
    {
        return 'mj-wrapper';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return array_merge(parent::getAllowedAttributes(), [
            'gap' => 'unit(px)',
        ]);
    }

    /**
     * Render wrapped children - each child gets its own <tr> for vertical stacking.
     *
     * This is the key difference from mj-section: wrapper renders each child section
     * in its own table row, while section renders all columns in a single row.
     */
    protected function renderWrappedChildren(): string
    {
        $containerWidth = $this->getContainerWidth();
        $output = '';

        foreach ($this->children as $child) {
            if ($child instanceof BodyComponent && $child::isRawElement()) {
                $output .= $child->render();
            } elseif ($child instanceof ComponentInterface) {
                $cssClass = $child->getAttribute('css-class');
                $outlookClass = (null !== $cssClass && '' !== $cssClass)
                    ? CssHelper::suffixCssClasses($cssClass, 'outlook')
                    : '';

                $tdAttributes = [
                    'align' => $child->getAttribute('align'),
                    'class' => $outlookClass,
                    'width' => "{$containerWidth}px",
                ];

                $output .= ConditionalTag::START_CONDITIONAL
                    .'<tr>'
                    .\sprintf('<td %s>', $this->htmlAttributes($tdAttributes))
                    .ConditionalTag::END_CONDITIONAL
                    .$child->render()
                    .ConditionalTag::START_CONDITIONAL
                    .'</td></tr>'
                    .ConditionalTag::END_CONDITIONAL;
            }
        }

        return $output;
    }
}
