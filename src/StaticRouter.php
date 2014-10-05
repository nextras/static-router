<?php

namespace Nextras\Routing;

use Nette;
use Nette\Application\IRouter;
use Nette\Application\Request as AppRequest;
use Nette\Http\IRequest as HttpRequest;
use Nette\Http\Url;


/**
 * Simple static router.
 */
class StaticRouter extends Nette\Object implements IRouter
{
	/** @var array (slug => Presenter:action) */
	private $tableIn;

	/** @var array (Presenter:action => slug) */
	private $tableOut;

	/** @var int */
	private $flags;


	/**
	 * @param array $routingTable Presenter:action => slug
	 * @param int   $flags        IRouter::ONE_WAY, IRouter::SECURED
	 */
	public function __construct(array $routingTable, $flags = 0)
	{
		$this->tableIn = array();
		$this->tableOut = $routingTable;
		$this->flags = $flags;

		foreach ($routingTable as $destination => $slug) {
			$this->tableIn[rtrim($slug, '/')] = $destination;
		}
	}


	/**
	 * Maps HTTP request to a Request object.
	 *
	 * @return AppRequest|NULL
	 */
	public function match(HttpRequest $httpRequest)
	{
		$slug = rtrim($httpRequest->getUrl()->getPathInfo(), '/');
		if (!isset($this->tableIn[$slug])) {
			return NULL;
		}

		$params = $httpRequest->getQuery();
		list($presenter, $params['action']) = explode(':', $this->tableIn[$slug]);

		return new AppRequest(
			$presenter,
			$httpRequest->getMethod(),
			$params,
			$httpRequest->getPost(),
			$httpRequest->getFiles(),
			array(AppRequest::SECURED => $httpRequest->isSecured())
		);
	}


	/**
	 * Constructs absolute URL from Request object.
	 *
	 * @return string|NULL
	 */
	public function constructUrl(AppRequest $appRequest, Url $refUrl)
	{
		if ($this->flags & self::ONE_WAY) {
			return NULL;
		}

		$presenter = $appRequest->getPresenterName();
		$params = $appRequest->getParameters();
		if (!isset($params['action']) || !is_string($params['action'])) {
			return NULL;
		}

		$key = $presenter . ':' . $params['action'];
		if (!isset($this->tableOut[$key])) {
			return NULL;
		}

		unset($params['action']);
		$schema = ($this->flags & self::SECURED ? 'https' : 'http') . '://';
		$slug = $this->tableOut[$key];
		$query = ($params ? '?' . http_build_query($params) : '');
		$url = $schema . $refUrl->getAuthority() . $refUrl->getBasePath() . $slug . $query;

		return $url;
	}

}
