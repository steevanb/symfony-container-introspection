[![version](https://img.shields.io/badge/version-1.0.2-green.svg)](https://github.com/steevanb/symfony-container-introspection/tree/1.0.2)
[![php](https://img.shields.io/badge/php-^7.1-blue.svg)](https://php.net)
[![symfony](https://img.shields.io/badge/symfony/dependency--injection-^3.4||^4.0-blue.svg)](https://symfony.com)
![Lines](https://img.shields.io/badge/code%20lines-738-green.svg)
![Total Downloads](https://poser.pugx.org/steevanb/symfony-container-introspection/downloads)
[![Scrutinizer](https://scrutinizer-ci.com/g/steevanb/symfony-container-introspection/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/steevanb/symfony-container-introspection/)

symfony-container-introspection
===============================

It helps you to know which services are registered, instanciated, public or private and list container parameters.

You have access to Container cache statistics: files count, count code lines and cache size.

With Symfony, a new profiler tab will appear:

![Symfony profiler tab](symfony_profiler_tab.png)

![Symfony profiler](symfony_profiler.png)

[Changelog](changelog.md)

Installation
============

```bash
composer require --dev steevanb/symfony-container-introspection ^1.0.2
```

If you use Symfony (and not just symfony/dependency-injection), you can add `ContainerIntrospectionBundle` to your Kernel:
```php
# app/AppKernel.php for Symfony 3.*
# src/Kernel.php for Symfony 4.*

class Kernel
{
    public function registerBundles()
    {
        if ($this->getEnvironment() === 'dev') {
            $bundles[] = new \steevanb\ContainerIntrospection\Bridge\ContainerIntrospectionBundle\ContainerIntrospectionBundle();
        }
    }
}
```
