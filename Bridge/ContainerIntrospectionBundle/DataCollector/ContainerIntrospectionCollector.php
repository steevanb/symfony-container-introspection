<?php

declare(strict_types=1);

namespace steevanb\ContainerIntrospection\Bridge\ContainerIntrospectionBundle\DataCollector;

use steevanb\ContainerIntrospection\ContainerIntrospectionService;
use Symfony\Component\HttpFoundation\{
    Request,
    Response
};
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ContainerIntrospectionCollector extends DataCollector
{
    /** @var ContainerIntrospectionService */
    protected $introspection;

    public function __construct(ContainerIntrospectionService $introspection)
    {
        $this->introspection = $introspection;
    }

    public function getName(): string
    {
        return 'steevanb.container_collector';
    }

    public function collect(Request $request, Response $response, \Exception $exception = null): void
    {
        $this->introspection->introspect();

        $this->data = [
            'containerCachePath' => $this->introspection->getContainerCachePath(),
            'containerCacheDir' => $this->introspection->getContainerCacheDir(),
            'countContainerCacheFiles' => $this->introspection->countContainerCacheFiles(),
            'countContainerCacheLines' => $this->introspection->countContainerCacheLines(),
            'containerCacheSize' => $this->introspection->getContainerCacheSize(),

            'countServices' => $this->introspection->countServices(),

            'instantiatedServices' => $this->introspection->getInstantiatedServices(),
            'countInstanciatedServices' => $this->introspection->countInstantiatedServices(),

            'publicServices' => $this->introspection->getPublicServices(),
            'countPublicServices' => $this->introspection->countPublicServices(),

            'removedServices' => $this->introspection->getRemovedServices(),
            'countRemovedServices' => $this->introspection->countRemovedServices(),

            'parameters' => $this->introspection->getParameters(),
            'countParameters' => $this->introspection->countParameters()
        ];
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function getContainerCachePath(): string
    {
        return $this->data['containerCachePath'];
    }

    public function getContainerCacheDir(): string
    {
        return $this->data['containerCacheDir'];
    }

    public function countContainerCacheFiles(): int
    {
        return $this->data['countContainerCacheFiles'];
    }

    public function countContainerCacheLines(): int
    {
        return $this->data['countContainerCacheLines'];
    }

    public function getContainerCacheSize(): int
    {
        return $this->data['containerCacheSize'];
    }

    public function getInstantiatedServices(): array
    {
        return $this->data['instantiatedServices'];
    }
    
    public function countInstantiatedServices(): int
    {
        return $this->data['countInstanciatedServices'];
    }

    public function getPublicServices(): array
    {
        return $this->data['publicServices'];
    }

    public function countPublicServices(): int
    {
        return $this->data['countPublicServices'];
    }

    public function getRemovedServices(): array
    {
        return $this->data['removedServices'];
    }

    public function countRemovedServices(): int
    {
        return $this->data['countRemovedServices'];
    }

    public function getParameters(): array
    {
        return $this->data['parameters'];
    }

    public function countParameters(): int
    {
        return $this->data['countParameters'];
    }

    public function countServices(): int
    {
        return $this->data['countServices'];
    }

    public function getInstantiatedInfos(string $id): ?array
    {
        $services = $this->getInstantiatedServices();

        return array_key_exists($id, $services) ? $services[$id] : null;
    }

    public function dumpParameterValue($data): void
    {
        echo var_export($data, true);
    }
}
