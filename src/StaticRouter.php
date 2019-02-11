<?php

namespace Nextras\Routing;

use Nette\Application\IRouter;
use Nette\Http\IRequest as HttpRequest;
use Nette\Http\Url;


/**
 * Simple static router.
 */
class StaticRouter implements IRouter
{
	/** @var array (Presenter:action => slug) */
	private $tableOut;

	/** @var int */
	private $flags;

	/** @var Url|null */
	private $lastRefUrl;

	/** @var string */
	private $lastBaseUrl;


	/**
	 * @param array $routingTable Presenter:action => slug
	 * @param int   $flags        IRouter::ONE_WAY, IRouter::SECURED
	 */
	public function __construct(array $routingTable, $flags = 0)
	{
		$this->tableOut = $routingTable;
		$this->flags = $flags;
	}


	/**
	 * Maps HTTP request to a Request object.
	 */
	public function match(HttpRequest $httpRequest): ?array
	{
		$slug = rtrim($httpRequest->getUrl()->getRelativePath(), '/');
		foreach ($this->tableOut as $destination2 => $slug2) {
			if ($slug === rtrim($slug2, '/')) {
				$destination = (string) $destination2;
				break;
			}
		}

		if (!isset($destination)) {
			return null;
		}

		$params = $httpRequest->getQuery();
		$pos = strrpos($destination, ':');
		$params['presenter'] = substr($destination, 0, (int) $pos);
		$params['action'] = substr($destination, $pos + 1);

		return $params;
	}


	/**
	 * Constructs absolute URL from Request object.
	 */
	public function constructUrl(array $params, Url $refUrl): ?string
	{
		if ($this->flags & self::ONE_WAY) {
			return null;
		}

		if (!isset($params['action']) || !is_string($params['action'])) {
			return null;
		}

		$key = $params['presenter'] . ':' . $params['action'];
		if (!isset($this->tableOut[$key])) {
			return null;
		}

		if ($this->lastRefUrl !== $refUrl) {
			$this->lastBaseUrl = $refUrl->getBaseUrl();
			$this->lastRefUrl = $refUrl;
		}

		unset($params['presenter'], $params['action']);
		$slug = $this->tableOut[$key];
		$query = (($tmp = http_build_query($params)) ? '?' . $tmp : '');
		$url = $this->lastBaseUrl . $slug . $query;

		return $url;
	}
}
