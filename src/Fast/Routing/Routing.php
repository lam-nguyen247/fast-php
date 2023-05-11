<?php
namespace Fast\Routing;

use Fast\Container;
use Fast\Http\Kernel;
use Fast\Http\Request;
use ReflectionException;
use Fast\Supports\Response\Response;
use Fast\Http\Exceptions\AppException;

class Routing {
	const ROUTING_SEPARATOR = '/';

	private array $routes = [];

	public function __construct(array $routes = []) {
		$this->routes = $routes;
	}

	/**
	 * Finding matching route
	 * @throws RouteException
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function find(): Response {
		$requestUrl = format_url($this->getRequestURL());
		$requestMethod = $this->getRequestMethod();
		$requestParams = explode(Routing::ROUTING_SEPARATOR, $requestUrl);
		foreach ($this->routes as $route) {
			$uri = $route->getUri();
			$method = $route->getMethods();
			$prefix = $route->getPrefix();
			if (!empty($prefix)) {
				$uri = self::ROUTING_SEPARATOR . implode(self::ROUTING_SEPARATOR, $prefix) . $uri;
			}

			$routeParams = explode(self::ROUTING_SEPARATOR, $uri);
			if (str_contains(strtolower($method), strtolower($requestMethod)) && count($requestParams) === count($routeParams)) {
				$checking = new HandleMatched(format_url($uri), $requestUrl);
				if ($checking->isMatched === true) {
					$next = new NextPasses($routeParams, $requestParams, $route);
					return $next->getResponse();
				}
			}
		}
		return $this->handleNotFound();
	}

	/**
	 * @throws ReflectionException
	 * @throws AppException
	 */
	public function getRequestURL(): string {
		$uri = urldecode(parse_url(app()->getRequest()->server->get('REQUEST_URI'), PHP_URL_PATH));
		return empty($uri) ? Routing::ROUTING_SEPARATOR : $uri;
	}

	/**
	 * @throws ReflectionException
	 * @throws AppException
	 */
	private function getRequestMethod(): string {
		return app()->getRequest()->server->get('REQUEST_METHOD') ?? 'GET';
	}

	/**
	 * Handle not found
	 *
	 * @return Response
	 * @throws AppException
	 * @throws ReflectionException
	 */
	private function handleNotFound(): Response {
		return response()->json(['status' => false, 'message' => 'File not found'], 404);
	}
}
