<?php
namespace Fast\Routing;

use Fast\Container;
use ReflectionException;
use Fast\Pipeline\Pipeline;
use Fast\Routing\RouteCollection;
use Fast\Http\Exceptions\AppException;

class NextPasses {
	/**
	 * @throws RouteException|AppException|ReflectionException
	 */
	public function __construct(array $routeParams, array $requestParams, RouteCollection $route) {
		$params = [];

		foreach ($routeParams as $key => $value) {
			if (preg_match('/^{\w+}$/', $value)) {
				$params[] = $requestParams[$key];
			}
		}

		$action = $route->getAction();

		$middlewares = $route->getMiddlewares();

		$next = match (true) {
			is_array($action) || is_string($action) => function () use ($route, $params) {
				$compile = new Compile($route, $params);
				return $compile->handle();
			},
			$action instanceof \Closure => $action,
			default => throw new RouteException('Action not implemented'),
		};
		$httpKernel = new \App\Http\Kernel(Container::getInstance());

		foreach ($middlewares as $middleware) {
			if (!isset($httpKernel->routeMiddlewares[$middleware])) {
				throw new RouteException("Middleware '{$middleware}' not found.");
			}
		}

		return (new Pipeline(Container::getInstance()))
			->send(app('request'))
			->through($middlewares)
			->then(function () use ($params, $next) {
				return $next(...$params);
			});
	}
}