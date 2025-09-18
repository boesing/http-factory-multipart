<?php

declare(strict_types=1);

namespace Boesing\Psr\Http\Message\Multipart;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/** @api */
final class HttpFactoryMultipartBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->services()
            ->defaults()->autowire()->autoconfigure()
            ->load('Boesing\\Psr\\Http\\Message\\Multipart\\', __DIR__)
            ->exclude([
                __DIR__ . '/ConfigProvider.php',
                __DIR__ . '/HttpFactoryMultipartBundle.php',
            ]);
    }
}
