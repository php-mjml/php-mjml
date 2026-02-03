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

namespace App;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\UX\StimulusBundle\StimulusBundle;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new TwigBundle();
        yield new StimulusBundle();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'demo-secret-change-me',
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => [
                'log' => true,
            ],
            'session' => [
                'handler_id' => null,
                'cookie_secure' => 'auto',
                'cookie_samesite' => 'lax',
                'storage_factory_id' => 'session.storage.factory.native',
            ],
            'mailer' => [
                'dsn' => '%env(MAILER_DSN)%',
            ],
            'rate_limiter' => [
                'verification_request' => [
                    'policy' => 'sliding_window',
                    'limit' => 3,
                    'interval' => '1 hour',
                ],
                'test_email_send' => [
                    'policy' => 'sliding_window',
                    'limit' => 5,
                    'interval' => '1 hour',
                ],
            ],
            'asset_mapper' => [
                'paths' => [
                    'assets/',
                ],
            ],
        ]);

        $container->extension('twig', [
            'default_path' => '%kernel.project_dir%/templates',
        ]);

        $container->services()
            ->load('App\\', __DIR__.'/*')
            ->autowire()
            ->autoconfigure()
        ;
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__.'/Controller/', 'attribute');
    }
}
