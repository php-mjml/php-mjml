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

namespace PhpMjml\Component\Context;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Validates and resolves navbar context settings.
 *
 * Navbar settings are passed from mj-navbar → mj-navbar-link.
 */
final class NavbarContextResolver
{
    public const KEY = 'navbar';

    /**
     * Resolve navbar settings with validation.
     *
     * @param array<string, mixed> $data Raw settings data
     *
     * @return array<string, string|null> Validated settings
     */
    public static function resolve(array $data): array
    {
        $resolver = new OptionsResolver();
        self::configureOptions($resolver);

        return $resolver->resolve($data);
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'baseUrl' => null,
        ]);

        $resolver->setAllowedTypes('baseUrl', ['null', 'string']);
    }
}
