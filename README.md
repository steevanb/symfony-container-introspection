[![Version](https://img.shields.io/badge/version-3.0.1-4B9081.svg)](https://github.com/steevanb/symfony-container-introspection/tree/3.0.1)
[![php](https://img.shields.io/badge/php-^7.1||^8.0-blue.svg)](https://php.net)
![Lines](https://img.shields.io/badge/code%20lines-927-green.svg)
![Lines](https://img.shields.io/badge/code%20lines-1,860-blue.svg)

symfony-container-introspection
===============================

It helps you to know which services are instanciated, removed, public and list container parameters.

You have access to Container cache statistics: files count, count code lines and cache size.

With Symfony, a new profiler tab will appear:

![Symfony profiler tab](symfony_profiler_tab.png)

![Symfony profiler](symfony_profiler.png)

[Changelog](changelog.md)

If you want to use it with `symfony/dependency-injection ^3.4`, use [steevanb/symfony-container-introspection ^1.0](https://github.com/steevanb/symfony-container-introspection/tree/1.0.x).

If you want to use it with `symfony/dependency-injection ^4.0`, use [steevanb/symfony-container-introspection ^1.1](https://github.com/steevanb/symfony-container-introspection/tree/1.1.x).

Installation
============

```bash
composer require --dev steevanb/symfony-container-introspection ^3.0
```

If you use Symfony (and not just `symfony/dependency-injection`), you can add `ContainerIntrospectionBundle` to your Kernel:
```php
# config/bundles.php
<?php

return [
    Steevanb\ContainerIntrospection\Bridge\ContainerIntrospectionBundle\ContainerIntrospectionBundle::class => ['dev' => true]
];
```
