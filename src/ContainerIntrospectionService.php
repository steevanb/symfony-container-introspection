<?php

declare(strict_types=1);

namespace Steevanb\ContainerIntrospection;

/** If you use https://github.com/Ocramius/ProxyManager */
use ProxyManager\Proxy\VirtualProxyInterface;
use Symfony\Component\DependencyInjection\{
    Container,
    Exception\ServiceNotFoundException
};
use Steevanb\ContainerIntrospection\Exception\ContainerIntrospectionException;

class ContainerIntrospectionService
{
    /** @var Container */
    protected $container;

    /** @var string */
    protected $containerClassName;

    /** @var string */
    protected $cacheDir;

    /** @var array<mixed> */
    protected $instanciatedServices = [];

    /** @var array<string> */
    protected $publicServices = [];

    /** @var array<string> */
    protected $removedServices = [];

    /** @var array<mixed>  */
    protected $parameters = [];

    /** @var string|null */
    protected $cachePath;

    /** @var int|null */
    protected $cacheFilesCount;

    /** @var int|null */
    protected $cacheLinesCount;

    /** @var int|null */
    protected $cacheSize;

    /** @var int|null */
    protected $countServices;

    /** @var bool */
    protected $introspectHasBeenCalled = false;

    /**
     * Yes, Container as dependency,
     * because we need to use \ReflectionClass and call getRemovedIds() on it to find services
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->containerClassName = get_class($container);
        $cacheDir = $container->getParameter('kernel.cache_dir');
        if (is_string($cacheDir) === false) {
            throw new ContainerIntrospectionException('Kernel cache directory not found.');
        }
        $this->cacheDir = $cacheDir;
    }

    public function introspect(): self
    {
        $this
            ->introspectInstantiatedServices()
            ->introspectPublicServices()
            ->introspectRemovedServices()
            ->introspectParameters()
            ->introspectCountServices()
            ->introspectCache();

        $this->introspectHasBeenCalled = true;

        return $this;
    }

    /** @return array<mixed> */
    public function getInstantiatedServices(): array
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return $this->instanciatedServices;
    }

    public function countInstantiatedServices(): int
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return count($this->instanciatedServices);
    }

    /** @return array<string> */
    public function getRemovedServices(): array
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return $this->removedServices;
    }

    public function countRemovedServices(): int
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return count($this->removedServices);
    }

    /** @return array<string> */
    public function getPublicServices(): array
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return $this->publicServices;
    }

    public function countPublicServices(): int
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return count($this->publicServices);
    }

    /** @return array<mixed> */
    public function getParameters(): array
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return $this->parameters;
    }

    public function countParameters(): int
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return count($this->parameters);
    }

    public function getContainerCachePath(): string
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return (string) $this->cachePath;
    }

    public function getContainerCacheDir(): string
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return basename($this->getContainerCachePath());
    }

    public function countContainerCacheFiles(): int
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return (int) $this->cacheFilesCount;
    }

    public function countContainerCacheLines(): int
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return (int) $this->cacheLinesCount;
    }

    public function getContainerCacheSize(): int
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return (int) $this->cacheSize;
    }

    public function countServices(): int
    {
        $this->assertIntrospectHasBeenCalled(__METHOD__);

        return (int) $this->countServices;
    }

    protected function assertIntrospectHasBeenCalled(string $method): self
    {
        if ($this->introspectHasBeenCalled === false) {
            throw new ContainerIntrospectionException('You must call introspect() before ' . $method . '().');
        }

        return $this;
    }

    /** @return mixed */
    protected function getPrivatePropertyValue(string $name)
    {
        $property = new \ReflectionProperty($this->containerClassName, $name);
        $property->setAccessible(true);
        $return = $property->getValue($this->container);
        $property->setAccessible(false);

        return $return;
    }

    /** @return array<mixed> */
    protected function getPrivateArrayPropertyValue(string $name): array
    {
        $return = $this->getPrivatePropertyValue($name);
        if (is_array($return) === false) {
            throw new ContainerIntrospectionException(
                'Container property $' . $name . ' should be an array but is not.'
            );
        }

        return $return;
    }

    /** @return class-string */
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

    protected function introspectInstantiatedServices(): self
    {
        $services = array_merge(
            $this->getPrivateArrayPropertyValue('services'),
            $this->getPrivateArrayPropertyValue('privates')
        );
        ksort($services);

        foreach ($services as $id => $service) {
            if (is_object($service)) {
                $ocramiusLazy = $service instanceof VirtualProxyInterface;
                $className = ($ocramiusLazy) ? get_parent_class($service) : get_class($service);
                if (is_string($className) === false) {
                    continue;
                }

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

                $value = null;
                $fqcn = $reflection->getName();
                $fileName = $reflection->getFileName();
            } else {
                $value = var_export($service, true);
                $fqcn = null;
                $fileName = null;
                $dependencies = [];
                $ocramiusLazy = false;
            }

            try {
                $this->container->get($id);
                $public = true;
            } catch (ServiceNotFoundException $e) {
                $public = false;
            }

            $this->instanciatedServices[$id] = [
                'value' => $value,
                'public' => $public,
                'fqcn' => $fqcn,
                'fileName' => $fileName,
                'dependencies' => $dependencies,
                'ocramiusLazy' => $ocramiusLazy
            ];
        }

        return $this;
    }

    protected function introspectRemovedServices(): self
    {
        $this->removedServices = array_keys($this->container->getRemovedIds());
        sort($this->removedServices);

        return $this;
    }

    protected function introspectPublicServices(): self
    {
        $fileMapServices = array_keys($this->getPrivateArrayPropertyValue('fileMap'));
        $methodMapServices = array_keys($this->getPrivateArrayPropertyValue('methodMap'));
        $removedServices = array_keys($this->container->getRemovedIds());
        $privateServices = array_keys($this->getPrivateArrayPropertyValue('privates'));
        $services = array_keys($this->getPrivateArrayPropertyValue('services'));

        /** @phpstan-ignore-next-line $publicServices (array<string>) does not accept array<int|string, int> */
        $this->publicServices =
            array_flip(
                array_unique(
                    array_merge($fileMapServices, $methodMapServices, $services)
                )
            );

        // It looks like fileMap and methodMap only registers public services, but I filter them to be sure
        foreach (array_merge($privateServices, $removedServices) as $privateServiceId) {
            unset($this->publicServices[$privateServiceId]);
        }
        $this->publicServices = array_flip($this->publicServices);
        sort($this->publicServices);

        return $this;
    }

    protected function introspectParameters(): self
    {
        $this->parameters = $this->getPrivateArrayPropertyValue('parameters');

        return $this;
    }

    protected function introspectCache(): self
    {
        $fileName = (new \ReflectionClass($this->getContainerCacheClassName()))
            ->getFileName();
        if (is_string($fileName) === false) {
            throw new ContainerIntrospectionException('Container cache file name not found.');
        }

        $this->cachePath = dirname($fileName);

        $cacheFiles = glob($this->getContainerCachePath() . '/*');
        if (is_array($cacheFiles) === false) {
            throw new ContainerIntrospectionException('Container cache files not found.');
        }
        $this->cacheFilesCount = count($cacheFiles);

        $this->cacheLinesCount = 0;
        $this->cacheSize = 0;
        foreach ($cacheFiles as $cacheFile) {
            $lines = file($cacheFile);
            if (is_array($lines) === false) {
                continue;
            }

            $this->cacheLinesCount += count($lines);
            $this->cacheSize += filesize($cacheFile);
        }

        return $this;
    }

    protected function introspectCountServices(): self
    {
        $this->countServices = count(
            array_unique(
                array_merge(
                    array_keys($this->getPrivateArrayPropertyValue('fileMap')),
                    array_keys($this->getPrivateArrayPropertyValue('methodMap')),
                    array_keys($this->container->getRemovedIds()),
                    array_keys($this->getPrivateArrayPropertyValue('privates')),
                    array_keys($this->getPrivateArrayPropertyValue('services'))
                )
            )
        );

        return $this;
    }
}
