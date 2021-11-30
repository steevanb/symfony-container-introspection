<?php

declare(strict_types=1);

namespace Steevanb\ContainerIntrospection\Bridge\ContainerIntrospectionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Loader\YamlFileLoader
};
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ContainerIntrospectionExtension extends Extension
{
    /** @param array<mixed> $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        (new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config')))
            ->load('services.yml');
    }
}
