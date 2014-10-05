<?php

namespace Nextras\Routing;

use Nette;
use Nette\Application\IRouter;
use Nette\Application\Request as AppRequest;
use Nette\Http\Request as HttpRequest;
use Nette\Http\UrlScript;
use Tester;
use Tester\Assert;
use Mockery;

require __DIR__ . '/../bootstrap.php';


class StaticRouterTest extends Tester\TestCase
{
	/** @var IRouter */
	private $router;


	protected function setUp()
	{
		parent::setUp();

		$this->router = new StaticRouter(array(
			'Homepage:default' => '',
			'Homepage:index' => 'index.php',
			'Article:view' => 'view/',
		));
	}


	public function testMatch()
	{
		$httpRequest = $this->createHttpRequest('http://localhost/web/', '/web/');
		$appRequest = $this->router->match($httpRequest);
		$this->assertAppRequest($appRequest, 'Homepage', array('action' => 'default'));

		$httpRequest = $this->createHttpRequest('http://localhost/web/index.php', '/web/index.php');
		$appRequest = $this->router->match($httpRequest);
		$this->assertAppRequest($appRequest, 'Homepage', array('action' => 'index'));

		$httpRequest = $this->createHttpRequest('http://localhost/web/view', '/web/');
		$appRequest = $this->router->match($httpRequest);
		$this->assertAppRequest($appRequest, 'Article', array('action' => 'view'));

		$httpRequest = $this->createHttpRequest('http://localhost/web/view/', '/web/');
		$appRequest = $this->router->match($httpRequest);
		$this->assertAppRequest($appRequest, 'Article', array('action' => 'view'));

		$httpRequest = $this->createHttpRequest('http://localhost/web/view?id=123&type=foo', '/web/');
		$appRequest = $this->router->match($httpRequest);
		$this->assertAppRequest($appRequest, 'Article', array('action' => 'view', 'id' => '123', 'type' => 'foo'));

		$httpRequest = $this->createHttpRequest('http://localhost/web/XXX/', '/web/');
		$appRequest = $this->router->match($httpRequest);
		$this->assertAppRequest($appRequest, NULL);

		$httpRequest = $this->createHttpRequest('http://localhost/view', '/');
		$appRequest = $this->router->match($httpRequest);
		$this->assertAppRequest($appRequest, 'Article', array('action' => 'view'));
	}


	public function testConstructUrl()
	{
		$refUrl = new UrlScript('http://localhost/web/foo/bar/baz');
		$refUrl->setScriptPath('/web/');

		$url = $this->router->constructUrl(
			new AppRequest('Homepage', 'GET', array()),
			$refUrl
		);
		Assert::null($url);

		$url = $this->router->constructUrl(
			new AppRequest('Homepage', 'GET', array('action' => 'default')),
			$refUrl
		);
		Assert::same('http://localhost/web/', $url);

		$url = $this->router->constructUrl(
			new AppRequest('Article', 'GET', array('action' => 'view')),
			$refUrl
		);
		Assert::same('http://localhost/web/view/', $url);

		$url = $this->router->constructUrl(
			new AppRequest('Article', 'GET', array('action' => 'view', 'a' => 1, 'b' => 2)),
			$refUrl
		);
		Assert::same('http://localhost/web/view/?a=1&b=2', $url);

		$url = $this->router->constructUrl(
			new AppRequest('Article', 'GET', array('action' => 'view', 'a' => NULL)),
			$refUrl
		);
		Assert::same('http://localhost/web/view/', $url);

		$url = $this->router->constructUrl(
			new AppRequest('Article', 'GET', array('action' => 'edit', 'a' => 1, 'b' => 2)),
			$refUrl
		);
		Assert::null($url);
	}


	private function createHttpRequest($url, $scriptPath)
	{
		$url = new UrlScript($url);
		$url->setScriptPath($scriptPath);
		$httpRequest = new HttpRequest($url);
		return $httpRequest;
	}


	private function assertAppRequest($appRequest, $presenter, $params = array())
	{
		if ($presenter) {
			Assert::type('Nette\Application\Request', $appRequest);
			Assert::same($presenter, $appRequest->getPresenterName());
			Assert::equal($params, $appRequest->getParameters());
		} else {
			Assert::null($appRequest);
		}
	}

}

$test = new StaticRouterTest;
$test->run();
