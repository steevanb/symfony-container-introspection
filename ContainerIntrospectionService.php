<?php
/**
 * Copyright (c) 2018 Ekosport <contact@groupefraseteya.com>
 *
 * This file is part of Ekosport website.
 *
 * Ekosport website can not be copied and/or distributed without the express permission of the CIO.
 */

declare(strict_types=1);

namespace steevanb\ContainerIntrospection;

use ProxyManager\Proxy\VirtualProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerIntrospectionService
{
    /** @var ContainerInterface */
    protected $container;

    /** @var string */
    protected $containerClassName;

    /** @var string */
    protected $cacheDir;

    /** Yes, Container as dependency, cause we need to use \ReflectionClass on it to find services */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->containerClassName = get_class($container);
        $this->cacheDir = $container->getParameter('kernel.cache_dir');
    }

    public function getRegisteredServices(): array
    {
        $fileMapServices = array_keys($this->getPrivatePropertyValue('fileMap'));
        $methodMapServices = array_keys($this->getPrivatePropertyValue('methodMap'));

        $return = array_merge($fileMapServices, $methodMapServices);
        sort($return);

        return $return;
    }

    public function countRegisteredServices(): int
    {
        return count($this->getRegisteredServices());
    }

    public function getInstantiatedServices(): array
    {
        $services = array_keys($this->getPrivatePropertyValue('services'));
        sort($services);

        $return = [];
        foreach ($services as $id) {
            $service = $this->container->get($id);
            $ocramiusLazy = $service instanceof VirtualProxyInterface;
            $className = ($ocramiusLazy) ? get_parent_class($service) : get_class($service);

            $reflection = new \ReflectionClass($className);
            $constructor = $reflection->getConstructor();
            $dependencies = [];
            if ($constructor instanceof \ReflectionMethod) {
                foreach ($constructor->getParameters() as $parameter) {
                    $dependencies[] = [
                        'type' => $parameter->getType() instanceof \ReflectionNamedType
                            ? $parameter->getType()->getName()
                            : null,
                        'name' => $parameter->getName()
                    ];
                }
            }

            $return[$id] = [
                'fqcn' => $reflection->getName(),
                'fileName' => $reflection->getFileName(),
                'dependencies' => $dependencies,
                'ocramiusLazy' => $ocramiusLazy
            ];
        }

        return $return;
    }

    public function countInstantiatedServices(): int
    {
        return count($this->getInstantiatedServices());
    }

    public function getPrivateServices(): array
    {
        $return = array_keys($this->getPrivatePropertyValue('privates'));
        sort($return);

        return $return;
    }

    public function countPrivateServices(): int
    {
        return count($this->getPrivatePropertyValue('privates'));
    }

    public function getPublicServices(): array
    {
        $registeredServices = $this->getRegisteredServices();
        $privateServices = $this->getPrivateServices();

        return array_diff($registeredServices, $privateServices);
    }

    public function countPublicServices(): int
    {
        return $this->countRegisteredServices() - $this->countPrivateServices();
    }

    public function getParameters(): array
    {
        return $this->getPrivatePropertyValue('parameters');
    }

    public function countParameters(): int
    {
        return count($this->getParameters());
    }

    public function getContainerCachePath(): string
    {
        return dirname(
            (new \ReflectionClass($this->getContainerCacheClassName()))
                ->getFileName()
        );
    }

    public function getContainerCacheDir(): string
    {
        return basename($this->getContainerCachePath());
    }

    public function countContainerCacheFiles(): int
    {
        return count(glob($this->getContainerCachePath() . '/*'));
    }

    public function countContainerCacheLines(): int
    {
        $return = 0;
        foreach (glob($this->getContainerCachePath() . '/*') as $cacheFile) {
            $return += count(file(($cacheFile)));
        }

        return $return;
    }

    public function getContainerCacheSize(): int
    {
        $return = 0;
        foreach (glob($this->getContainerCachePath() . '/*') as $cacheFile) {
            $return += filesize($cacheFile);
        }

        return $return;
    }

    protected function getPrivatePropertyValue(string $name)
    {
        $property = new \ReflectionProperty($this->containerClassName, $name);
        $property->setAccessible(true);
        $return = $property->getValue($this->container);
        $property->setAccessible(false);

        return $return;
    }

    protected function getContainerCacheClassName(): string
    {
        $reflection = new \ReflectionClass(get_class($this->container));
        while (
            substr(
                $reflection->getFileName() === false ? '' : $reflection->getFileName(),
                0,
                strlen($this->cacheDir)
            ) !== $this->cacheDir
            && substr($reflection->getName(), -16) !== 'ProjectContainer'
        ) {
            $parentClass = $reflection->getParentClass();
            if ($parentClass === false) {
                throw new \Exception('Container cache file not found.');
            }
            $reflection = new \ReflectionClass($parentClass);
        }

        return $reflection->getName();
    }
}
