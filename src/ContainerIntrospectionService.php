<?php

declare(strict_types=1);

namespace Steevanb\ContainerIntrospection;

/** If you use https://github.com/Ocramius/ProxyManager */
use ProxyManager\Proxy\VirtualProxyInterface;
use Symfony\Component\DependencyInjection\{
    Container,
    Exception\ServiceNotFoundException
};

class ContainerIntrospectionService
{
    /** @var Container */
    protected $container;

    /** @var string */
    protected $containerClassName;

    /** @var string */
    protected $cacheDir;

    /** @var string[] */
    protected $instanciatedServices = [];

    /** @var string[] */
    protected $publicServices = [];

    /** @var string[] */
    protected $privateServices = [];

    /** @var string[] */
    protected $removedServices = [];

    protected $parameters = [];

    /** @var ?string */
    protected $cachePath;

    /** @var ?int */
    protected $cacheFilesCount;

    /** @var ?int */
    protected $cacheLinesCount;

    /** @var ?int */
    protected $cacheSize;

    /** @var ?int */
    protected $countServices;

    /**
     * Yes, Container as dependency,
     * because we need to use \ReflectionClass and call getRemovedIds() on it to find services
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->containerClassName = get_class($container);
        $this->cacheDir = $container->getParameter('kernel.cache_dir');
    }

    public function introspect(): self
    {
        return $this
            ->introspectInstantiatedServices()
            ->introspectPublicServices()
            ->introspectRemovedServices()
            ->introspectParameters()
            ->introspectCountServices()
            ->introspectCache();
    }

    public function getInstantiatedServices(): array
    {
        return $this->instanciatedServices;
    }

    public function countInstantiatedServices(): int
    {
        return count($this->instanciatedServices);
    }

    public function getRemovedServices(): array
    {
        return $this->removedServices;
    }

    public function countRemovedServices(): int
    {
        return count($this->removedServices);
    }

    public function getPublicServices(): array
    {
        return $this->publicServices;
    }

    public function countPublicServices(): int
    {
        return count($this->publicServices);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function countParameters(): int
    {
        return count($this->parameters);
    }

    public function getContainerCachePath(): string
    {
        return $this->cachePath;
    }

    public function getContainerCacheDir(): string
    {
        return basename($this->getContainerCachePath());
    }

    public function countContainerCacheFiles(): int
    {
        return $this->cacheFilesCount;
    }

    public function countContainerCacheLines(): int
    {
        return $this->cacheLinesCount;
    }

    public function getContainerCacheSize(): int
    {
        return $this->cacheSize;
    }

    public function countServices(): int
    {
        return $this->countServices;
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

    protected function introspectInstantiatedServices(): self
    {
        $services = array_merge(
            $this->getPrivatePropertyValue('services'),
            $this->getPrivatePropertyValue('privates')
        );
        ksort($services);

        foreach ($services as $id => $service) {
            if (is_object($service)) {
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
        $fileMapServices = array_keys($this->getPrivatePropertyValue('fileMap'));
        $methodMapServices = array_keys($this->getPrivatePropertyValue('methodMap'));
        $removedServices = array_keys($this->container->getRemovedIds());
        $privateServices = array_keys($this->getPrivatePropertyValue('privates'));
        $services = array_keys($this->getPrivatePropertyValue('services'));

        $this->publicServices = array_flip(array_unique(array_merge($fileMapServices, $methodMapServices, $services)));

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
        $this->parameters = $this->getPrivatePropertyValue('parameters');

        return $this;
    }

    protected function introspectCache(): self
    {
        $this->cachePath = dirname(
            (new \ReflectionClass($this->getContainerCacheClassName()))
                ->getFileName()
        );

        $this->cacheFilesCount = count(glob($this->getContainerCachePath() . '/*'));

        $this->cacheLinesCount = 0;
        $this->cacheSize = 0;
        foreach (glob($this->getContainerCachePath() . '/*') as $cacheFile) {
            $this->cacheLinesCount += count(file(($cacheFile)));
            $this->cacheSize += filesize($cacheFile);
        }

        return $this;
    }

    protected function introspectCountServices(): self
    {
        $this->countServices = count(
            array_unique(
                array_merge(
                    array_keys($this->getPrivatePropertyValue('fileMap')),
                    array_keys($this->getPrivatePropertyValue('methodMap')),
                    array_keys($this->container->getRemovedIds()),
                    array_keys($this->getPrivatePropertyValue('privates')),
                    array_keys($this->getPrivatePropertyValue('services'))
                )
            )
        );

        return $this;
    }
}
