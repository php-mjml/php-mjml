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

final class Spacer extends BodyComponent
{
    protected static bool $endingTag = true;

    public static function getComponentName(): string
    {
        return 'mj-spacer';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'border' => 'string',
            'border-bottom' => 'string',
            'border-left' => 'string',
            'border-right' => 'string',
            'border-top' => 'string',
            'container-background-color' => 'color',
            'padding-bottom' => 'unit(px,%)',
            'padding-left' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'padding-top' => 'unit(px,%)',
            'padding' => 'unit(px,%){1,4}',
            'height' => 'unit(px,%)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'height' => '20px',
        ];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        return [
            'div' => [
                'height' => $this->getAttribute('height'),
                'line-height' => $this->getAttribute('height'),
            ],
        ];
    }

    public function render(): string
    {
        return \sprintf(
            '<div %s>&#8202;</div>',
            $this->htmlAttributes(['style' => 'div']),
        );
    }
}
