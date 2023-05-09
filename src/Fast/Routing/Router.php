<?php
namespace Fast\Routing;

use ReflectionException;
use Fast\Routing\RouteCollection;
use Fast\Http\Exceptions\AppException;
use Fast\Http\Exceptions\UnknownException;

class Router {
	private array $middlewares = [];

	private string $prefix = '';

	private string $name = '';

	private string $namespace;

	private array $except;

	private array $resources = [];

	private array $routes = [];

	/**
	 * Add routing
	 *
	 * @param string $methods
	 * @param string $uri
	 * @param mixed $action
	 *
	 * @return RouteCollection
	 */
	private function addRoute(string $methods, string $uri, mixed $action): RouteCollection {
		$router = new RouteCollection($methods, $uri, $this->name, $action, $this->middlewares, $this->prefix, $this->namespace);
		$this->routes[] = $router;
		return $router;
	}

	public function get(string $uri, mixed $action): RouteCollection {
		return $this->addRoute('GET', $uri, $action);
	}

	public function post(string $uri, mixed $action): RouteCollection {
		return $this->addRoute('POST', $uri, $action);
	}

	public function put(string $uri, mixed $action): RouteCollection {
		return $this->addRoute('PUT', $uri, $action);
	}

	public function patch(string $uri, mixed $action): RouteCollection {
		return $this->addRoute('PATCH', $uri, $action);
	}

	public function any(string $uri, mixed $action): RouteCollection {
		return $this->addRoute('GET|POST', $uri, $action);
	}

	public function delete(string $uri, mixed $action): RouteCollection {
		return $this->addRoute('DELETE', $uri, $action);
	}

	public function middleware(mixed $middleware): Router {
		if (!is_array($middleware)) {
			$this->middlewares[] = $middleware;
		} else {
			$this->middlewares = array_merge($this->middlewares, $middleware);
		}
		return $this;
	}

	public function register(): bool {
		$this->middlewares = [];
		$this->prefix = '';
		$this->namespace = '';
		$this->name = '';
		$this->except = [];
		$this->resources = [];
		return true;
	}

	public function prefix(string $prefix): Router {
		$this->prefix = $prefix;
		return $this;
	}

	public function namespace(string $namespace): Router {
		$this->namespace = $namespace;
		return $this;
	}

	public function group(string $path): Router {
		if (file_exists($path)) {
			require $path;
			return $this;
		}
		throw new AppException("$path not found");
	}

	public function resource(string $uri, mixed $action): RouteResource {
		$resource = [
			[
				compact('uri', 'action'),
			],
			$this->name, $this->middlewares, $this->prefix, $this->namespace,
		];
		$routeResource = new RouteResource(...$resource);
		$this->routes[] = $routeResource;
		return $routeResource;
	}

	/**
	 * Register many routes
	 *
	 * @param array $resources
	 *
	 * @return RouteResource
	 */
	public function resources(array $resources): RouteResource {
		$middlewares = $this->middlewares;
		$prefix = $this->prefix;
		$namespace = $this->namespace;
		$name = $this->name;
		$items = [];
		foreach ($resources as $key => $resource) {
			$items[] = [
				'uri' => $key,
				'action' => $resource,
			];
		}
		$resources = [$items, $name, $middlewares, $prefix, $namespace];
		$routeResource = new RouteResource(...$resources);
		$this->routes[] = $routeResource;
		return $routeResource;
	}

	/**
	 * Get list routes
	 *
	 * @return array
	 */
	public function routes(): array {
		return $this->routes;
	}

	/**
	 * Run the routing
	 *
	 * @return mixed
	 * @throws RouteException
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function run(): mixed {
		$routing = new Routing($this->collect());

		return $routing->find();
	}

	/**
	 * Collect all routing defined
	 *
	 * @return array
	 */
	public function collect(): array {
		$routes = [];

		foreach ($this->routes() as $object) {
			if ($object instanceof RouteResource) {
				$routes = array_merge($routes, $object->parse());
			} else {
				$routes[] = $object;
			}
		}

		return $routes;
	}

	/**
	 * Callable action to controller method
	 *
	 * @param array $action
	 * @param array $params = []
	 *
	 * @return mixed
	 * @throws AppException
	 * @throws RouteException
	 * @throws UnknownException
	 */
	public function callableAction(array $action, array $params = []): mixed {
		$rc = new RouteCollection(
			__FUNCTION__,
			__FUNCTION__,
			__FUNCTION__,
			$action,
			__FUNCTION__,
			__FUNCTION__,
			null
		);

		$compile = new Compile($rc, $params);

		return $compile->handle();
	}
}