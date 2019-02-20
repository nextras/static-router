<?php declare(strict_types = 1);

namespace Nextras\Routing;

use Nette\Http\UrlScript;
use Nette\Http\Request as HttpRequest;
use Nette\Routing\Route;
use Nette\Routing\RouteList;
use Nette\Routing\Router;

require __DIR__ . '/../../vendor/autoload.php';


class_exists(\Nette\Http\Request::class);
class_exists(\Nette\Http\Url::class);
class_exists(\Nette\Http\UrlScript::class);
class_exists(\Nette\Routing\Route::class);
class_exists(\Nette\Routing\RouteList::class);
class_exists(\Nette\Utils\Callback::class);
class_exists(\Nette\Utils\ObjectHelpers::class);
class_exists(\Nette\Utils\Strings::class);
class_exists(\Nextras\Routing\StaticRouter::class);

$routers = [
	'StaticRoute' => function () {
		$router = new StaticRouter([
			'Aaaaaaaa:aaaaaaaa' => 'slug-aaaaaaaa',
			'Bbbbbbbb:bbbbbbbb' => 'slug-bbbbbbbb',
			'Cccccccc:cccccccc' => 'slug-cccccccc',
			'Dddddddd:dddddddd' => 'slug-dddddddd',
			'Eeeeeeee:eeeeeeee' => 'slug-eeeeeeee',
			'Zzzzzzzz:vvvvvvvv' => 'slug-vvvvvvvv',
			'Zzzzzzzz:wwwwwwww' => 'slug-wwwwwwww',
			'Zzzzzzzz:xxxxxxxx' => 'slug-xxxxxxxx',
			'Zzzzzzzz:yyyyyyyy' => 'slug-yyyyyyyy',
			'Zzzzzzzz:zzzzzzzz' => 'slug-zzzzzzzz',
		]);

		return $router;
	},

	'Route + RouteList' => function () {
		$router = new RouteList();
		$router->addRoute('slug-aaaaaaaa', ['presenter' => 'Aaaaaaaa', 'action' => 'aaaaaaaa']);
		$router->addRoute('slug-bbbbbbbb', ['presenter' => 'Bbbbbbbb', 'action' => 'bbbbbbbb']);
		$router->addRoute('slug-cccccccc', ['presenter' => 'Cccccccc', 'action' => 'cccccccc']);
		$router->addRoute('slug-dddddddd', ['presenter' => 'Dddddddd', 'action' => 'dddddddd']);
		$router->addRoute('slug-eeeeeeee', ['presenter' => 'Eeeeeeee', 'action' => 'eeeeeeee']);
		$router->addRoute('slug-vvvvvvvv', ['presenter' => 'Zzzzzzzz', 'action' => 'vvvvvvvv']);
		$router->addRoute('slug-wwwwwwww', ['presenter' => 'Zzzzzzzz', 'action' => 'wwwwwwww']);
		$router->addRoute('slug-xxxxxxxx', ['presenter' => 'Zzzzzzzz', 'action' => 'xxxxxxxx']);
		$router->addRoute('slug-yyyyyyyy', ['presenter' => 'Zzzzzzzz', 'action' => 'yyyyyyyy']);
		$router->addRoute('slug-zzzzzzzz', ['presenter' => 'Zzzzzzzz', 'action' => 'zzzzzzzz']);

		return $router;
	},

	'Route + global filter' => function () {
		$tableOut = [
			'Aaaaaaaa:aaaaaaaa' => 'slug-aaaaaaaa',
			'Bbbbbbbb:bbbbbbbb' => 'slug-bbbbbbbb',
			'Cccccccc:cccccccc' => 'slug-cccccccc',
			'Dddddddd:dddddddd' => 'slug-dddddddd',
			'Eeeeeeee:eeeeeeee' => 'slug-eeeeeeee',
			'Zzzzzzzz:vvvvvvvv' => 'slug-vvvvvvvv',
			'Zzzzzzzz:wwwwwwww' => 'slug-wwwwwwww',
			'Zzzzzzzz:xxxxxxxx' => 'slug-xxxxxxxx',
			'Zzzzzzzz:yyyyyyyy' => 'slug-yyyyyyyy',
			'Zzzzzzzz:zzzzzzzz' => 'slug-zzzzzzzz',
		];

		$router = new Route('[<slug .*>]', [
			null => [
				Route::FILTER_IN => function (array $params) use ($tableOut) {
					foreach ($tableOut as $destination2 => $slug2) {
						if ($params['slug'] === rtrim($slug2, '/')) {
							$destination = $destination2;
							break;
						}
					}

					if (!isset($destination)) {
						return null;
					}

					$pos = strrpos($destination, ':');
					$params['presenter'] = substr($destination, 0, $pos);
					$params['action'] = substr($destination, $pos + 1);
					unset($params['slug']);

					return $params;
				},
				Route::FILTER_OUT => function (array $params) use ($tableOut) {
					if (!isset($params['presenter'], $params['action']) || !is_string($params['action'])) {
						return null;
					}

					$key = $params['presenter'] . ':' . $params['action'];
					if (!isset($tableOut[$key])) {
						return null;
					}

					$params['slug'] = $tableOut[$key];
					unset($params['presenter'], $params['action']);

					return $params;
				},
			],
		]);

		return $router;
	},
];

$tests = [
	'__construct' => function ($count, $routerFactory) {
		for ($i = 0; $i < $count; $i++) {
			$router = $routerFactory();
		}

		assert($router instanceof Router);
	},

	'match-first' => function ($count, $routerFactory) {
		$httpRequest = new HttpRequest(new UrlScript('http://localhost/slug-aaaaaaaa'));
		$router = $routerFactory();

		for ($i = 0; $i < $count; $i++) {
			$appRequest = $router->match($httpRequest);
		}

		assert($appRequest === ['presenter' => 'Aaaaaaaa', 'action' => 'aaaaaaaa']);
	},

	'match-last' => function ($count, $routerFactory) {
		$httpRequest = new HttpRequest(new UrlScript('http://localhost/slug-zzzzzzzz'));
		$router = $routerFactory();

		for ($i = 0; $i < $count; $i++) {
			$appRequest = $router->match($httpRequest);
		}

		assert($appRequest === ['presenter' => 'Zzzzzzzz', 'action' => 'zzzzzzzz']);
	},

	'match-invalid' => function ($count, $routerFactory) {
		$httpRequest = new HttpRequest(new UrlScript('http://localhost/slug-not-found'));
		$router = $routerFactory();

		for ($i = 0; $i < $count; $i++) {
			$appRequest = $router->match($httpRequest);
		}

		assert($appRequest === null);
	},

	'constructUrl-first' => function ($count, $routerFactory) {
		$appRequest = ['presenter' => 'Aaaaaaaa', 'action' => 'aaaaaaaa'];
		$refUrl = new UrlScript('http://localhost/');
		$router = $routerFactory();

		for ($i = 0; $i < $count; $i++) {
			$url = $router->constructUrl($appRequest, $refUrl);
		}

		assert($url === 'http://localhost/slug-aaaaaaaa');
	},

	'constructUrl-last' => function ($count, $routerFactory) {
		$appRequest = ['presenter' => 'Zzzzzzzz', 'action' => 'zzzzzzzz'];
		$refUrl = new UrlScript('http://localhost/');
		$router = $routerFactory();

		for ($i = 0; $i < $count; $i++) {
			$url = $router->constructUrl($appRequest, $refUrl);
		}

		assert($url === 'http://localhost/slug-zzzzzzzz');
	},

	'constructUrl-invalid' => function ($count, $routerFactory) {
		$appRequest = ['presenter' => 'Invalid', 'action' => 'default'];
		$refUrl = new UrlScript('http://localhost/');
		$router = $routerFactory();

		for ($i = 0; $i < $count; $i++) {
			$url = $router->constructUrl($appRequest, $refUrl);
		}

		assert($url === null);
	},
];

$testCount = 10000;
foreach ($tests as $testName => $testCallback) {
	printf("Test %s:\n", $testName);
	foreach ($routers as $routerName => $routerFactory) {
		$time = -microtime(true);
		$testCallback($testCount, $routerFactory);
		$time += microtime(true);
		printf("  %-25s%5.0f ms\n", $routerName, $time * 1e3);
	}
	printf("\n");
}
