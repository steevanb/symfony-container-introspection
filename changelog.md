### [1.1.0](../../compare/1.0.2...1.1.0) - 2018-09-02

- Compatibility with symfony/dependency-injection ^4.0, remove symfony/dependency-injection ^3.4 compatibility

### [1.0.2](../../compare/1.0.1...1.0.2) - 2018-08-06

- Do not call `ContainerInterface::get()` to retrieve informations on service, we already got it. It remove deprecated for private services in Symfony 3, and retrieve private service informations in Symfony 4.
- Increase `ContainerIntrospectionService::countInstantiatedServices()` performances

### [1.0.1](../../compare/1.0.0...1.0.1) - 2018-06-06

- Fix when service is not an object

### 1.0.0 - 2018-06-06

- Get registered services: `ContainerIntrospectionService::getRegisteredServices()`
- Get instantiated services: `ContainerIntrospectionService::getInstantiatedServices()`
- Get public services: `ContainerIntrospectionService::getPublicServices()`
- Get private services: `ContainerIntrospectionService::getPrivateServices()`
- Get container parameters: `ContainerIntrospectionService::getParameters()`
- Get cache dir: `ContainerIntrospectionService::getContainerCacheDir()`
- Count Container cache files: `ContainerIntrospectionService::countContainerCacheFiles()`
- Count Container cache lines: `ContainerIntrospectionService::countContainerCacheLines()`
- Count Container cache size: `ContainerIntrospectionService::getContainerCacheSize()`
- Create bridge for Symfony: `ContainerIntrospectionBundle`
