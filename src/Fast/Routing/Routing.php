<?php
namespace Fast\Routing;

use ReflectionException;
use Fast\Http\Exceptions\AppException;

class Routing
{
	const ROUTING_SEPARATOR = "/";

	private  array $routes = [];

	public function __construct(array $routes = [])
	{
		$this->routes = $routes;
	}

	/**
	 * Finding matching route
	 * @throws RouteException
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function find(): mixed {
		$requestUrl = format_url($this->getRequestURL());
		$requestMethod = $this->getRequestMethod();
		$requestParams = explode(Routing::ROUTING_SEPARATOR, $requestUrl);

		foreach ($this->routes as $route) {
			$uri = $route->getUri();
			$method = $route->getMethods();
			$prefix = $route->getPrefix();

			if(!empty($prefix)) {
				$uri =	format_url( self::ROUTING_SEPARATOR . implode(self::ROUTING_SEPARATOR, $prefix) . $uri);
			}

			$routeParams = explode(self::ROUTING_SEPARATOR, $uri);
			if (str_contains(strtolower($method), strtolower($requestMethod))) {
				if (count($requestParams) === count($routeParams)) {
					$checking = new HandleMatched($uri, $requestUrl);
					if ($checking->isMatched === true) {
						return new NextPasses($routeParams, $requestParams, $route);
					}
				}
			}
		}
		return $this->handleNotFound();
	}

	public function getRequestURL(): string
	{
		$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
		return empty($uri) ? Routing::ROUTING_SEPARATOR : $uri;
	}

	private function getRequestMethod(): string
	{
		return $_SERVER['REQUEST_METHOD'] ?? "GET";
	}

	/**
	 * Handle not found
	 *
	 * @return mixed
	 * @throws AppException
	 * @throws ReflectionException
	 */
	private function handleNotFound(): mixed {
		return app()->make('view')->render('404');
	}
}