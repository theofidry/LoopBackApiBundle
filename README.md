# LoopBackApiBundle

[![Package version](http://img.shields.io/packagist/v/theofidry/loopback-api-bundle.svg?style=flat-square)](https://packagist.org/packages/theofidry/loopback-api-bundle)
[![Build Status](https://img.shields.io/travis/theofidry/LoopBackApiBundle.svg?&branch=master&style=flat-square)](https://travis-ci.org/theofidry/LoopBackApiBundle?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/e7cbcdb9-f024-43e0-b7ba-7a002949aa98.svg?style=flat-square)](https://insight.sensiolabs.com/projects/e7cbcdb9-f024-43e0-b7ba-7a002949aa98)
[![Dependency Status](https://www.versioneye.com/user/projects/55887c45306662001a0000ce/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55887c45306662001a0000ce)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/theofidry/LoopBackApiBundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/theofidry/LoopBackApiBundle/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/theofidry/LoopBackApiBundle.svg?b=master&style=flat-square)](https://scrutinizer-ci.com/g/theofidry/LoopBackApiBundle/?branch=master)

[LoopBack](http://loopback.io/) like Doctrine ORM filters for [DunglasApiBundle](https://github.com/dunglas/DunglasApiBundle).

**DEPRECATED**: You should use [ApiPlatform Core](https://github.com/api-platform/core) now.

## Documentation

1. [Install](#install)
2. [Introduction](src/Resources/doc/introduction.md)
3. [Order filter](src/Resources/doc/order-filter.md)
4. [Where filter](src/Resources/doc/where-filter.md)
  1. [Special values](src/Resources/doc/where-filter.md#special-values)
    1. [Search on embedded relation property](src/Resources/doc/where-filter.md#search-on-embedded-relation-property)
    2. [Boolean values](src/Resources/doc/where-filter.md#boolean-values)
    3. [Date values](src/Resources/doc/where-filter.md#date-values)
    4. [Null values](src/Resources/doc/where-filter.md#null-values)
    5. [Empty values](src/Resources/doc/where-filter.md#empty-values)
  2. [Operators](src/Resources/doc/where-filter.md#operators)
    1. [or](src/Resources/doc/where-filter.md#or)
    2. [gt(e)/lt(e)](Resources/doc/where-filter.md#gtelte)
    3. [between](src/Resources/doc/where-filter.md#between)
    4. [neq](src/Resources/doc/where-filter.md#neq)
    5. [like/nlike](src/Resources/doc/where-filter.md#likenlike)


## Install

You can use [Composer](https://getcomposer.org/) to install the bundle to your project:

```bash
composer require theofidry/loopback-api-bundle
```

Then, enable the bundle by updating your `app/config/AppKernel.php` file to enable the bundle:
```php
<?php
// app/config/AppKernel.php

public function registerBundles()
{
    //...
    $bundles[] = new Fidry\LoopBackApiBundle\LoopBackApiBundle();

    return $bundles;
}
```

## Credits

This bundle is developed by [Théo FIDRY](https://github.com/theofidry).

## License

[![license](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)
