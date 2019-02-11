<?php

namespace Nextras\Routing;

use Nette;
use Nette\Application\Request as AppRequest;
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
	private $tableOut = array(
		'Homepage:default' => '',
		'Homepage:index' => 'index.php',
		'Article:view' => 'view/',
		'Admin:Dashboard:view' => 'admin/dashboard',
	);


	/**
	 * @dataProvider provideMatchData
	 */
	public function testMatch($fullUrl, $scriptPath, array $expected = NULL)
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
		return array(
			array(
				'http://localhost/web/',
				'/web/',
				array('presenter' => 'Homepage', 'action' => 'default')
			),
			array(
				'http://localhost/web/index.php',
				'/web/index.php',
				array('presenter' => 'Homepage', 'action' => 'index')
			),
			array(
				'http://localhost/web/view',
				'/web/',
				array('presenter' => 'Article', 'action' => 'view')
			),
			array(
				'http://localhost/web/view/',
				'/web/',
				array('presenter' => 'Article', 'action' => 'view')
			),
			array(
				'http://localhost/web/view?id=123&type=foo',
				'/web/',
				array('id' => '123', 'type' => 'foo', 'presenter' => 'Article', 'action' => 'view')
			),
			array(
				'http://localhost/web/XXX/',
				'/web/',
				NULL
			),
			array(
				'http://localhost/view',
				'/',
				array('presenter' => 'Article', 'action' => 'view')
			),
			array(
				'http://localhost/web/admin/dashboard',
				'/web/',
				array('presenter' => 'Admin:Dashboard', 'action' => 'view')
			),
		);
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
		return array(
			array(
				array('presenter' => 'Homepage'),
				NULL,
			),
			array(
				array('presenter' => 'Homepage', 'action' => 'default'),
				'http://localhost/web/',
			),
			array(
				array('presenter' => 'Article', 'action' => 'view'),
				'http://localhost/web/view/',
			),
			array(
				array('presenter' => 'Article', 'action' => 'view', 'a' => 1, 'b' => 2),
				'http://localhost/web/view/?a=1&b=2',
			),
			array(
				array('presenter' => 'Article', 'action' => 'view', 'a' => NULL),
				'http://localhost/web/view/',
			),
			array(
				array('presenter' => 'Article', 'action' => 'edit', 'a' => 1, 'b' => 2), NULL
			),
			array(
				array('presenter' => 'Admin:Dashboard', 'action' => 'view'),
				'http://localhost/web/admin/dashboard',
			),
		);
	}


	public function testOneWayFlag()
	{
		$router = new StaticRouter($this->tableOut, StaticRouter::ONE_WAY);
		Assert::null($router->constructUrl(
			array('presenter' => 'Homepage', 'action' => 'default'),
			new UrlScript('http://localhost/')
		));
	}


	public function testHttps()
	{
		$router = new StaticRouter($this->tableOut);
		$url = $router->constructUrl(
			array('presenter' => 'Article', 'action' => 'view'),
			new UrlScript('https://localhost/')
		);

		Assert::same('https://localhost/view/', $url);
	}

}

$test = new StaticRouterTest;
$test->run();
