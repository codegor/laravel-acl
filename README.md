
# Codegor/laravel-acl

[![Laravel](https://img.shields.io/badge/Laravel-~5.0-orange.svg?style=flat-square)](http://laravel.com)
[![Source](http://img.shields.io/badge/source-codegor/laravel--acl-blue.svg?style=flat-square)](https://github.com/codegor/laravel-acl/)
[![Build Status](http://img.shields.io/travis/kodeine/laravel--acl/master.svg?style=flat-square)](https://travis-ci.org/codegor/laravel-acl)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)
[![Total Downloads](http://img.shields.io/packagist/dt/kodeine/laravel-acl.svg?style=flat-square)](https://packagist.org/packages/codegor/laravel-acl)

Laravel ACL adds role based permissions to built in Auth System of Laravel 5.6. ACL middleware protects routes.

# Table of Contents
* [Requirements](#requirements)
* [Getting Started](#getting-started)


# <a name="requirements"></a>Requirements

* This package requires PHP 7.0+

# <a name="getting-started"></a>Getting Started

1. Require the package in your `composer.json` and update your dependency with `composer update`:

```
"require": {
...
"codegor/laravel-acl": "~0.5",
...
},
```

4. Add the middleware to your `app/Http/Kernel.php`.

```php
protected $routeMiddleware = [

....
'acl' => 'Codegor\Acl\Http\Middleware\Acl',

];
```