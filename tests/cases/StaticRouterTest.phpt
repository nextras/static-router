<?php

namespace Nextras\Routing;

use Nette;
use Nette\Application\Request as AppRequest;
use Nette\Http\Request as HttpRequest;
use Nette\Http\UrlScript;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


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
	public function testMatch($fullUrl, $scriptPath, $presenter, array $params = array())
	{
		$url = new UrlScript($fullUrl);
		$url->setScriptPath($scriptPath);
		$httpRequest = new HttpRequest($url);

		$router = new StaticRouter($this->tableOut);
		$appRequest = $router->match($httpRequest);

		if ($presenter === NULL) {
			Assert::null($appRequest);

		} else {
			Assert::type('Nette\Application\Request', $appRequest);
			Assert::same($presenter, $appRequest->getPresenterName());
			Assert::same($params, $appRequest->getParameters());
		}
	}


	public function provideMatchData()
	{
		return array(
			array(
				'http://localhost/web/',
				'/web/',
				'Homepage',
				array('action' => 'default')
			),
			array(
				'http://localhost/web/index.php',
				'/web/index.php',
				'Homepage',
				array('action' => 'index')
			),
			array(
				'http://localhost/web/view',
				'/web/',
				'Article',
				array('action' => 'view')
			),
			array(
				'http://localhost/web/view/',
				'/web/',
				'Article',
				array('action' => 'view')
			),
			array(
				'http://localhost/web/view?id=123&type=foo',
				'/web/',
				'Article',
				array('id' => '123', 'type' => 'foo', 'action' => 'view')
			),
			array(
				'http://localhost/web/XXX/',
				'/web/',
				NULL
			),
			array(
				'http://localhost/view',
				'/',
				'Article',
				array('action' => 'view')
			),
			array(
				'http://localhost/web/admin/dashboard',
				'/web/',
				'Admin:Dashboard',
				array('action' => 'view')
			),
		);
	}


	/**
	 * @dataProvider provideConstructUrlData
	 */
	public function testConstructUrl($presenter, $params, $url)
	{
		$refUrl = new UrlScript('http://localhost/web/foo/bar/baz');
		$refUrl->setScriptPath('/web/');

		$router = new StaticRouter($this->tableOut);
		Assert::same($url, $router->constructUrl(
			new AppRequest($presenter, 'GET', $params),
			$refUrl
		));
	}


	public function provideConstructUrlData()
	{
		return array(
			array(
				'Homepage',
				array(),
				NULL,
			),
			array(
				'Homepage',
				array('action' => 'default'),
				'http://localhost/web/',
			),
			array(
				'Article',
				array('action' => 'view'),
				'http://localhost/web/view/',
			),
			array(
				'Article',
				array('action' => 'view', 'a' => 1, 'b' => 2),
				'http://localhost/web/view/?a=1&b=2',
			),
			array(
				'Article',
				array('action' => 'view', 'a' => NULL),
				'http://localhost/web/view/',
			),
			array(
				'Article',
				array('action' => 'edit', 'a' => 1, 'b' => 2), NULL
			),
			array(
				'Admin:Dashboard',
				array('action' => 'view'),
				'http://localhost/web/admin/dashboard',
			),
		);
	}


	public function testOneWayFlag()
	{
		$router = new StaticRouter($this->tableOut, StaticRouter::ONE_WAY);
		Assert::null($router->constructUrl(
			new AppRequest('Homepage', 'GET', array('action' => 'default')),
			new UrlScript('http://localhost/')
		));
	}


	public function testSecuredFlag()
	{
		$router = new StaticRouter($this->tableOut, StaticRouter::SECURED);
		Assert::same('https://localhost/', $router->constructUrl(
			new AppRequest('Homepage', 'GET', array('action' => 'default')),
			new UrlScript('http://localhost/')
		));
	}

}

$test = new StaticRouterTest;
$test->run();
