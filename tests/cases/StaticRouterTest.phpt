<?php

namespace Nextras\Routing;

use Nette\Http\Request as HttpRequest;
use Nette\Http\UrlScript;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


/**
 * @testCase
 */
class StaticRouterTest extends Tester\TestCase
{
	/** @var array */
	private $tableOut = [
		'Homepage:default' => '',
		'Homepage:index' => 'index.php',
		'Article:view' => 'view/',
		'Admin:Dashboard:view' => 'admin/dashboard',
	];


	/**
	 * @dataProvider provideMatchData
	 */
	public function testMatch($fullUrl, $scriptPath, ?array $expected = null)
	{
		$url = new UrlScript($fullUrl);
		$url->setScriptPath($scriptPath);
		$httpRequest = new HttpRequest($url);

		$router = new StaticRouter($this->tableOut);
		$params = $router->match($httpRequest);

		Assert::same($expected, $params);
	}


	public function provideMatchData()
	{
		return [
			[
				'http://localhost/web/',
				'/web/',
				['presenter' => 'Homepage', 'action' => 'default'],
			],
			[
				'http://localhost/web/index.php',
				'/web/index.php',
				['presenter' => 'Homepage', 'action' => 'index'],
			],
			[
				'http://localhost/web/view',
				'/web/',
				['presenter' => 'Article', 'action' => 'view'],
			],
			[
				'http://localhost/web/view/',
				'/web/',
				['presenter' => 'Article', 'action' => 'view'],
			],
			[
				'http://localhost/web/view?id=123&type=foo',
				'/web/',
				['id' => '123', 'type' => 'foo', 'presenter' => 'Article', 'action' => 'view'],
			],
			[
				'http://localhost/web/XXX/',
				'/web/',
				null,
			],
			[
				'http://localhost/view',
				'/',
				['presenter' => 'Article', 'action' => 'view'],
			],
			[
				'http://localhost/web/admin/dashboard',
				'/web/',
				['presenter' => 'Admin:Dashboard', 'action' => 'view'],
			],
		];
	}


	/**
	 * @dataProvider provideConstructUrlData
	 */
	public function testConstructUrl(array $params, $url)
	{
		$refUrl = new UrlScript('http://localhost/web/foo/bar/baz');
		$refUrl->setScriptPath('/web/');

		$router = new StaticRouter($this->tableOut);
		Assert::same($url, $router->constructUrl($params, $refUrl));
	}


	public function provideConstructUrlData()
	{
		return [
			[
				['presenter' => 'Homepage'],
				null,
			],
			[
				['presenter' => 'Homepage', 'action' => 'default'],
				'http://localhost/web/',
			],
			[
				['presenter' => 'Article', 'action' => 'view'],
				'http://localhost/web/view/',
			],
			[
				['presenter' => 'Article', 'action' => 'view', 'a' => 1, 'b' => 2],
				'http://localhost/web/view/?a=1&b=2',
			],
			[
				['presenter' => 'Article', 'action' => 'view', 'a' => null],
				'http://localhost/web/view/',
			],
			[
				['presenter' => 'Article', 'action' => 'edit', 'a' => 1, 'b' => 2], null,
			],
			[
				['presenter' => 'Admin:Dashboard', 'action' => 'view'],
				'http://localhost/web/admin/dashboard',
			],
		];
	}


	public function testOneWayFlag()
	{
		$router = new StaticRouter($this->tableOut, StaticRouter::ONE_WAY);
		Assert::null($router->constructUrl(
			['presenter' => 'Homepage', 'action' => 'default'],
			new UrlScript('http://localhost/')
		));
	}


	public function testHttps()
	{
		$router = new StaticRouter($this->tableOut);
		$url = $router->constructUrl(
			['presenter' => 'Article', 'action' => 'view'],
			new UrlScript('https://localhost/')
		);

		Assert::same('https://localhost/view/', $url);
	}
}

$test = new StaticRouterTest();
$test->run();
