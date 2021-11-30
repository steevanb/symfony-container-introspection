<?php

declare(strict_types=1);

namespace Steevanb\ContainerIntrospection\Bridge\ContainerIntrospectionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\{
    ContainerBuilder,
    Loader
};
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ContainerIntrospectionExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
