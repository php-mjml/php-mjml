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

final class SocialElement extends BodyComponent
{
    private const IMG_BASE_URL = 'https://www.mailjet.com/images/theme/v1/icons/ico-social/';

    /**
     * @var array<string, array{src: string, 'background-color': string, 'share-url'?: string}>
     */
    private const DEFAULT_SOCIAL_NETWORKS = [
        'facebook' => [
            'share-url' => 'https://www.facebook.com/sharer/sharer.php?u=[[URL]]',
            'background-color' => '#3b5998',
            'src' => self::IMG_BASE_URL.'facebook.png',
        ],
        'twitter' => [
            'share-url' => 'https://twitter.com/intent/tweet?url=[[URL]]',
            'background-color' => '#55acee',
            'src' => self::IMG_BASE_URL.'twitter.png',
        ],
        'x' => [
            'share-url' => 'https://twitter.com/intent/tweet?url=[[URL]]',
            'background-color' => '#000000',
            'src' => self::IMG_BASE_URL.'twitter-x.png',
        ],
        'google' => [
            'share-url' => 'https://plus.google.com/share?url=[[URL]]',
            'background-color' => '#dc4e41',
            'src' => self::IMG_BASE_URL.'google-plus.png',
        ],
        'pinterest' => [
            'share-url' => 'https://pinterest.com/pin/create/button/?url=[[URL]]&media=&description=',
            'background-color' => '#bd081c',
            'src' => self::IMG_BASE_URL.'pinterest.png',
        ],
        'linkedin' => [
            'share-url' => 'https://www.linkedin.com/shareArticle?mini=true&url=[[URL]]&title=&summary=&source=',
            'background-color' => '#0077b5',
            'src' => self::IMG_BASE_URL.'linkedin.png',
        ],
        'instagram' => [
            'background-color' => '#3f729b',
            'src' => self::IMG_BASE_URL.'instagram.png',
        ],
        'web' => [
            'background-color' => '#4BADE9',
            'src' => self::IMG_BASE_URL.'web.png',
        ],
        'snapchat' => [
            'background-color' => '#FFFA54',
            'src' => self::IMG_BASE_URL.'snapchat.png',
        ],
        'youtube' => [
            'background-color' => '#EB3323',
            'src' => self::IMG_BASE_URL.'youtube.png',
        ],
        'tumblr' => [
            'share-url' => 'https://www.tumblr.com/widgets/share/tool?canonicalUrl=[[URL]]',
            'background-color' => '#344356',
            'src' => self::IMG_BASE_URL.'tumblr.png',
        ],
        'github' => [
            'background-color' => '#000000',
            'src' => self::IMG_BASE_URL.'github.png',
        ],
        'xing' => [
            'share-url' => 'https://www.xing.com/app/user?op=share&url=[[URL]]',
            'background-color' => '#296366',
            'src' => self::IMG_BASE_URL.'xing.png',
        ],
        'vimeo' => [
            'background-color' => '#53B4E7',
            'src' => self::IMG_BASE_URL.'vimeo.png',
        ],
        'medium' => [
            'background-color' => '#000000',
            'src' => self::IMG_BASE_URL.'medium.png',
        ],
        'soundcloud' => [
            'background-color' => '#EF7F31',
            'src' => self::IMG_BASE_URL.'soundcloud.png',
        ],
        'dribbble' => [
            'background-color' => '#D95988',
            'src' => self::IMG_BASE_URL.'dribbble.png',
        ],
    ];
    protected static bool $endingTag = true;

    public static function getComponentName(): string
    {
        return 'mj-social-element';
    }

    /**
     * @return array<string, string>
     */
    public static function getAllowedAttributes(): array
    {
        return [
            'align' => 'enum(left,center,right)',
            'icon-position' => 'enum(left,right)',
            'background-color' => 'color',
            'color' => 'color',
            'border-radius' => 'unit(px)',
            'font-family' => 'string',
            'font-size' => 'unit(px)',
            'font-style' => 'string',
            'font-weight' => 'string',
            'href' => 'string',
            'icon-size' => 'unit(px,%)',
            'icon-height' => 'unit(px,%)',
            'icon-padding' => 'unit(px,%){1,4}',
            'line-height' => 'unit(px,%,)',
            'name' => 'string',
            'padding-bottom' => 'unit(px,%)',
            'padding-left' => 'unit(px,%)',
            'padding-right' => 'unit(px,%)',
            'padding-top' => 'unit(px,%)',
            'padding' => 'unit(px,%){1,4}',
            'text-padding' => 'unit(px,%){1,4}',
            'rel' => 'string',
            'src' => 'string',
            'srcset' => 'string',
            'sizes' => 'string',
            'alt' => 'string',
            'title' => 'string',
            'target' => 'string',
            'text-decoration' => 'string',
            'vertical-align' => 'enum(top,middle,bottom)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getDefaultAttributes(): array
    {
        return [
            'alt' => '',
            'align' => 'left',
            'icon-position' => 'left',
            'color' => '#000',
            'border-radius' => '3px',
            'font-family' => 'Ubuntu, Helvetica, Arial, sans-serif',
            'font-size' => '13px',
            'line-height' => '1',
            'padding' => '4px',
            'text-padding' => '4px 4px 4px 0',
            'target' => '_blank',
            'text-decoration' => 'none',
            'vertical-align' => 'middle',
        ];
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    public function getStyles(): array
    {
        $socialAttrs = $this->getSocialAttributes();
        $iconSize = $socialAttrs['icon-size'];
        $iconHeight = $socialAttrs['icon-height'];
        $backgroundColor = $socialAttrs['background-color'];

        return [
            'td' => [
                'padding' => $this->getAttribute('padding'),
                'padding-top' => $this->getAttribute('padding-top'),
                'padding-right' => $this->getAttribute('padding-right'),
                'padding-bottom' => $this->getAttribute('padding-bottom'),
                'padding-left' => $this->getAttribute('padding-left'),
                'vertical-align' => $this->getAttribute('vertical-align'),
            ],
            'table' => [
                'background' => $backgroundColor,
                'border-radius' => $this->getAttribute('border-radius'),
                'width' => $iconSize,
            ],
            'icon' => [
                'padding' => $this->getAttribute('icon-padding'),
                'font-size' => '0',
                'height' => $iconHeight ?? $iconSize,
                'vertical-align' => 'middle',
                'width' => $iconSize,
            ],
            'img' => [
                'border-radius' => $this->getAttribute('border-radius'),
                'display' => 'block',
            ],
            'tdText' => [
                'vertical-align' => 'middle',
                'padding' => $this->getAttribute('text-padding'),
                'text-align' => $this->getAttribute('align'),
            ],
            'text' => [
                'color' => $this->getAttribute('color'),
                'font-size' => $this->getAttribute('font-size'),
                'font-weight' => $this->getAttribute('font-weight'),
                'font-style' => $this->getAttribute('font-style'),
                'font-family' => $this->getAttribute('font-family'),
                'line-height' => $this->getAttribute('line-height'),
                'text-decoration' => $this->getAttribute('text-decoration'),
            ],
        ];
    }

    public function render(): string
    {
        $socialAttrs = $this->getSocialAttributes();
        $hasLink = null !== $this->getAttribute('href');
        $iconPosition = $this->getAttribute('icon-position');

        $icon = $this->renderIcon($socialAttrs, $hasLink);
        $content = $this->renderTextContent($socialAttrs, $hasLink);

        $parts = 'left' === $iconPosition
            ? $icon.$content
            : $content.$icon;

        return \sprintf(
            '<tr %s>%s</tr>',
            $this->htmlAttributes([
                'class' => $this->getAttribute('css-class'),
            ]),
            $parts,
        );
    }

    /**
     * Get the social network configuration merged with explicit attributes.
     *
     * @return array{href: ?string, src: ?string, srcset: ?string, sizes: ?string, 'icon-size': ?string, 'icon-height': ?string, 'background-color': ?string}
     */
    private function getSocialAttributes(): array
    {
        $name = $this->getAttribute('name');
        $socialNetwork = self::getSocialNetworkConfig($name);

        $href = $this->getAttribute('href');
        if (null !== $href && isset($socialNetwork['share-url'])) {
            $href = str_replace('[[URL]]', $href, $socialNetwork['share-url']);
        }

        return [
            'href' => $href,
            'icon-size' => $this->getAttribute('icon-size') ?? ($socialNetwork['icon-size'] ?? null),
            'icon-height' => $this->getAttribute('icon-height') ?? ($socialNetwork['icon-height'] ?? null),
            'srcset' => $this->getAttribute('srcset') ?? ($socialNetwork['srcset'] ?? null),
            'sizes' => $this->getAttribute('sizes') ?? ($socialNetwork['sizes'] ?? null),
            'src' => $this->getAttribute('src') ?? ($socialNetwork['src'] ?? null),
            'background-color' => $this->getAttribute('background-color') ?? ($socialNetwork['background-color'] ?? null),
        ];
    }

    /**
     * Get the social network configuration by name.
     *
     * @return array{src?: string, 'background-color'?: string, 'share-url'?: string, 'icon-size'?: string, 'icon-height'?: string, srcset?: string, sizes?: string}
     */
    private static function getSocialNetworkConfig(?string $name): array
    {
        if (null === $name) {
            return [];
        }

        // Check for base network
        if (isset(self::DEFAULT_SOCIAL_NETWORKS[$name])) {
            return self::DEFAULT_SOCIAL_NETWORKS[$name];
        }

        // Check for -noshare variant
        if (str_ends_with($name, '-noshare')) {
            $baseName = substr($name, 0, -8);
            if (isset(self::DEFAULT_SOCIAL_NETWORKS[$baseName])) {
                $config = self::DEFAULT_SOCIAL_NETWORKS[$baseName];
                $config['share-url'] = '[[URL]]';

                return $config;
            }
        }

        return [];
    }

    /**
     * @param array{href: ?string, src: ?string, srcset: ?string, sizes: ?string, 'icon-size': ?string, 'icon-height': ?string, 'background-color': ?string} $socialAttrs
     */
    private function renderIcon(array $socialAttrs, bool $hasLink): string
    {
        $iconSize = $socialAttrs['icon-size'];
        $iconWidth = null !== $iconSize ? (int) $iconSize : null;

        $img = \sprintf(
            '<img %s />',
            $this->htmlAttributes([
                'alt' => $this->getAttribute('alt'),
                'title' => $this->getAttribute('title'),
                'src' => $socialAttrs['src'],
                'style' => 'img',
                'width' => $iconWidth,
                'sizes' => $socialAttrs['sizes'],
                'srcset' => $socialAttrs['srcset'],
            ]),
        );

        if ($hasLink) {
            $img = \sprintf(
                '<a %s>%s</a>',
                $this->htmlAttributes([
                    'href' => $socialAttrs['href'],
                    'rel' => $this->getAttribute('rel'),
                    'target' => $this->getAttribute('target'),
                ]),
                $img,
            );
        }

        return \sprintf(
            '<td %s><table %s><tbody><tr><td %s>%s</td></tr></tbody></table></td>',
            $this->htmlAttributes(['style' => 'td']),
            $this->htmlAttributes([
                'border' => '0',
                'cellpadding' => '0',
                'cellspacing' => '0',
                'role' => 'presentation',
                'style' => 'table',
            ]),
            $this->htmlAttributes(['style' => 'icon']),
            $img,
        );
    }

    /**
     * @param array{href: ?string, src: ?string, srcset: ?string, sizes: ?string, 'icon-size': ?string, 'icon-height': ?string, 'background-color': ?string} $socialAttrs
     */
    private function renderTextContent(array $socialAttrs, bool $hasLink): string
    {
        $content = $this->getContent();
        if ('' === $content) {
            return '';
        }

        if ($hasLink) {
            $textElement = \sprintf(
                '<a %s> %s </a>',
                $this->htmlAttributes([
                    'href' => $socialAttrs['href'],
                    'style' => 'text',
                    'rel' => $this->getAttribute('rel'),
                    'target' => $this->getAttribute('target'),
                ]),
                $content,
            );
        } else {
            $textElement = \sprintf(
                '<span %s> %s </span>',
                $this->htmlAttributes(['style' => 'text']),
                $content,
            );
        }

        return \sprintf(
            '<td %s>%s</td>',
            $this->htmlAttributes(['style' => 'tdText']),
            $textElement,
        );
    }
}
