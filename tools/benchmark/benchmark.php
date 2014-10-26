<?php

namespace Nextras\Routing;

use Nette\Application\IRouter;
use Nette\Application\Request as AppRequest;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Http\UrlScript;
use Nette\Http\Request as HttpRequest;

require __DIR__ . '/../../vendor/autoload.php';


class_exists('Nextras\Routing\StaticRouter');
class_exists('Nette\Application\Routers\Route');
class_exists('Nette\Application\Routers\RouteList');
class_exists('Nette\Utils\Strings');
class_exists('Nette\Utils\Callback');

$routers = array(
	'StaticRoute' => function () {
		$router = new StaticRouter(array(
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
		));

		return $router;
	},

	'Route + RouteList' => function () {
		$router = new RouteList();
		$router[] = new Route('slug-aaaaaaaa', 'Aaaaaaaa:aaaaaaaa');
		$router[] = new Route('slug-bbbbbbbb', 'Bbbbbbbb:bbbbbbbb');
		$router[] = new Route('slug-cccccccc', 'Cccccccc:cccccccc');
		$router[] = new Route('slug-dddddddd', 'Dddddddd:dddddddd');
		$router[] = new Route('slug-eeeeeeee', 'Eeeeeeee:eeeeeeee');
		$router[] = new Route('slug-vvvvvvvv', 'Zzzzzzzz:vvvvvvvv');
		$router[] = new Route('slug-wwwwwwww', 'Zzzzzzzz:wwwwwwww');
		$router[] = new Route('slug-xxxxxxxx', 'Zzzzzzzz:xxxxxxxx');
		$router[] = new Route('slug-yyyyyyyy', 'Zzzzzzzz:yyyyyyyy');
		$router[] = new Route('slug-zzzzzzzz', 'Zzzzzzzz:zzzzzzzz');

		return $router;
	},

	'Route + global filter' => function () {
		$tableOut = array(
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
		);

		$router = new Route('[<slug .*>]', array(
			NULL => array(
				Route::FILTER_IN => function (array $params) use ($tableOut) {
					foreach ($tableOut as $destination2 => $slug2) {
						if ($params['slug'] === rtrim($slug2, '/')) {
							$destination = $destination2;
							break;
						}
					}

					if (!isset($destination)) {
						return NULL;
					}

					$pos = strrpos($destination, ':');
					$params['presenter'] = substr($destination, 0, $pos);
					$params['action'] = substr($destination, $pos + 1);
					unset($params['slug']);

					return $params;
				},
				Route::FILTER_OUT => function (array $params) use ($tableOut) {
					if (!isset($params['presenter'], $params['action']) || !is_string($params['action'])) {
						return NULL;
					}

					$key = $params['presenter'] . ':' . $params['action'];
					if (!isset($tableOut[$key])) {
						return NULL;
					}

					$params['slug'] = $tableOut[$key];
					unset($params['presenter'], $params['action']);

					return $params;
				},
			)
		));

		return $router;
	},
);

$tests = array(
	'__construct' => function ($count, $routerFactory) {
		for ($i = 0; $i < $count; $i++) {
			$router = $routerFactory();
		}

		assert($router instanceof IRouter);
	},

	'match-first' => function ($count, $routerFactory) {
		$httpRequest = new HttpRequest(new UrlScript('http://localhost/slug-aaaaaaaa'));
		$router = $routerFactory();

		for ($i = 0; $i < $count; $i++) {
			$appRequest = $router->match($httpRequest);
		}

		assert($appRequest->getPresenterName() === 'Aaaaaaaa');
		assert($appRequest->getParameters() === array('action' => 'aaaaaaaa'));
	},

	'match-last' => function ($count, $routerFactory) {
		$httpRequest = new HttpRequest(new UrlScript('http://localhost/slug-zzzzzzzz'));
		$router = $routerFactory();

		for ($i = 0; $i < $count; $i++) {
			$appRequest = $router->match($httpRequest);
		}

		assert($appRequest->getPresenterName() === 'Zzzzzzzz');
		assert($appRequest->getParameters() === array('action' => 'zzzzzzzz'));
	},

	'match-invalid' => function ($count, $routerFactory) {
		$httpRequest = new HttpRequest(new UrlScript('http://localhost/slug-not-found'));
		$router = $routerFactory();

		for ($i = 0; $i < $count; $i++) {
			$appRequest = $router->match($httpRequest);
		}

		assert($appRequest === NULL);
	},

	'constructUrl-first' => function ($count, $routerFactory) {
		$appRequest = new AppRequest('Aaaaaaaa', 'GET', array('action' => 'aaaaaaaa'));
		$refUrl = new UrlScript('http://localhost/');
		$router = $routerFactory();

		for ($i = 0; $i < $count; $i++) {
			$url = $router->constructUrl($appRequest, $refUrl);
		}

		assert($url === 'http://localhost/slug-aaaaaaaa');
	},

	'constructUrl-last' => function ($count, $routerFactory) {
		$appRequest = new AppRequest('Zzzzzzzz', 'GET', array('action' => 'zzzzzzzz'));
		$refUrl = new UrlScript('http://localhost/');
		$router = $routerFactory();

		for ($i = 0; $i < $count; $i++) {
			$url = $router->constructUrl($appRequest, $refUrl);
		}

		assert($url === 'http://localhost/slug-zzzzzzzz');
	},

	'constructUrl-invalid' => function ($count, $routerFactory) {
		$appRequest = new AppRequest('Invalid', 'GET', array('action' => 'default'));
		$refUrl = new UrlScript('http://localhost/');
		$router = $routerFactory();

		for ($i = 0; $i < $count; $i++) {
			$url = $router->constructUrl($appRequest, $refUrl);
		}

		assert($url === NULL);
	},
);

$testCount = 1000;
foreach ($tests as $testName => $testCallback) {
	printf("Test %s:\n", $testName);
	foreach ($routers as $routerName => $routerFactory) {
		$time = -microtime(TRUE);
		$testCallback($testCount, $routerFactory);
		$time += microtime(TRUE);
		printf("  %-25s%5.0f ms\n", $routerName, $time * 1e3);
	}
	printf("\n");
}
