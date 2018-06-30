
# Codegor/laravel-acl

[![Laravel](https://img.shields.io/badge/Laravel-~5.6-orange.svg?style=flat-square)](http://laravel.com)
[![Source](http://img.shields.io/badge/source-codegor/laravel--acl-blue.svg?style=flat-square)](https://github.com/codegor/laravel-acl/)
[![Build Status](http://img.shields.io/travis/codegor/laravel--acl/master.svg?style=flat-square)](https://travis-ci.org/codegor/laravel-acl)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)
[![Total Downloads](http://img.shields.io/packagist/dt/codegor/laravel-acl.svg?style=flat-square)](https://packagist.org/packages/codegor/laravel-acl)

Laravel ACL adds role based permissions to built in Auth System of Laravel 5.6. ACL middleware protects routes. Useful when laravel use for server api(with resource controller). 
Current ACL can control resource by its statuses (very usfull if you need control what actions need deny).
If model has some status, for example, model has status 'active' and you want to deny action 'update' - with this ACL you can set this in the config at state field (see below).

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

2. Add the package to your application service providers in `config/app.php`.

```php
'providers' => [

Illuminate\Auth\AuthServiceProvider::class,
Illuminate\Broadcasting\BroadcastServiceProvider::class,
...
Codegor\Acl\Providers\AclServiceProvider::class,

],
'aliases' => [

'App' => Illuminate\Support\Facades\App::class,
'Artisan' => Illuminate\Support\Facades\Artisan::class,
...
'Acl' => Codegor\Acl\Facades\Acl::class
]
```

3. Publish the package config to your application. if you want def migration has on migrations folder at root folder of packege.

```
$ php artisan vendor:publish --provider="Codegor\Acl\Providers\AclServiceProvider"
```

4. Add the middleware to your `app/Http/Kernel.php`.

```php
protected $routeMiddleware = [

....
'acl' => 'Codegor\Acl\Http\Middleware\Acl',

];
```

5. Add the Acl trait to your `User` model.

```php
use Codegor\Acl\Traits\Acl;

class User extends Model
{
use ... Acl;
}
```

6. Config your acl on config/acl.php (Detail on the comments at config/acl.php file).

```php
return [
  'config' => [
    'role_source' => 'config' // 'config' || 'DB'
	...
  ],
  'permissions' => [
      'admin' => (object) [
        'role' => 'admin',
        'type' => 'all allow', // or 'all deny'
        'list' => [] // if in table - need in json formate
      ],
	  ...
  ]
  'state' => [
    'admins' => [ // resourse
      'active' =>[ // status #1
        'activate'
      ],
      'inactive' =>[ // status #2
        'update'
      ],
    ],
    ...
  ]
];
```

That's All! 

For creating permission list you can exec artisan command 'php artisan route:list' and you can see your rout table and col route name, this col you are need for list in the permission list (at middleware col you can see your acl middleware with others middleware). 
Acl works only if you apply 'acl' middleware. 
(For internal needs you can use Acl::getPointsApp() which return the list of all permissions)