Nextras StaticRouter
====================

[![Build Status](https://github.com/nextras/static-router/workflows/QA/badge.svg?branch=master)](https://github.com/nextras/static-router/actions?query=workflow%3AQA+branch%3Amaster)
[![Downloads this Month](https://img.shields.io/packagist/dm/nextras/static-router.svg?style=flat)](https://packagist.org/packages/nextras/static-router)
[![Stable Version](https://img.shields.io/packagist/v/nextras/static-router.svg?style=flat)](https://packagist.org/packages/nextras/static-router)


### Installation

Add to your composer.json:

```
"require": {
	"nextras/static-router": "~2.0"
}
```


### Example

```php
use Nextras\Routing\StaticRouter;

$router = new StaticRouter(['Homepage:default' => 'index.php'], StaticRouter::ONE_WAY);

$router = new StaticRouter([
	'Homepage:default' => '',
	'Auth:signIn' => 'sign-in',
	'Auth:signOut' => 'sign-out',
	'Auth:signUp' => 'sign-up',
]);
```
