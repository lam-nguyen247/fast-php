<?php
namespace Fast\Routing;

use Closure;
use ReflectionException;
use Fast\Supports\Facades\Route;
use Fast\Routing\RouteCollection;
use Fast\Supports\Response\Response;
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
	 * The route group attribute stack.
	 *
	 * @var array
	 */
	protected array $groupStack = [];

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
		$namespace = $this->namespace;
		$uri = $this->prefix . '/' . $uri;
		$router = new RouteCollection($methods, $uri, $this->name, $action, $this->middlewares, $this->prefix, $namespace);
		$this->routes[$methods][$uri] = $router;
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
			$this->middlewares[$middleware] = $middleware;
		} else {
			$this->middlewares = array_merge($this->middlewares, $middleware);
		}
		return $this;
	}

	public function prefix(string $prefix): Router {
		$this->prefix = $prefix;
		return $this;
	}

	public function namespace(string $namespace): Router {
		$this->namespace = $namespace;
		return $this;
	}

	/**
	 * @throws AppException
	 */
	public function path(string $path): Router {
		if (file_exists($path)) {
			require $path;
			return $this;
		}
		throw new AppException("$path not found");
	}

	/**
	 * Create a route group with shared attributes.
	 *
	 * @param array $attributes
	 * @param Closure|string $callback
	 * @return void
	 */
	public function group(array $attributes, mixed $callback = null): void {
		$this->updateGroupStack($attributes);
		//register routes belong current group.
		$this->loadRoutes($callback);
		$this->updateBeforeGroupStack($attributes);
	}

	/**
	 * Update the group stack with the given attributes.
	 *
	 * @param array $attributes
	 * @return void
	 */
	protected function updateGroupStack(array $attributes): void {
		$this->prefix = RouteGroup::formatPrefix($attributes['prefix'] ?? '', $this->prefix);
		$this->namespace = RouteGroup::formatNamespace($attributes['namespace'] ?? '', $this->namespace);
		if(isset($attributes['middleware'])){
			$this->middlewares[$attributes['middleware']] = $attributes['middleware'];
		}
	}

	/**
	 * Update the group stack before finish loadRoutes.
	 *
	 * @param array $attributes
	 * @return void
	 */
	protected function updateBeforeGroupStack(array $attributes): void {
		if(isset($attributes['prefix'])) {
			$this->prefix = str_replace($attributes['prefix'] . '/', '', $this->prefix);
		}
		if(isset($attributes['middleware'])){
			array_pop($this->middlewares);
		}
		if(isset($attributes['namespace'])) {
			$this->namespace = str_replace($attributes['namespace'], '', $this->namespace);
		}
	}

	/**
	 * Load the provided routes.
	 *
	 * @param string|Closure $routes
	 * @return void
	 */
	protected function loadRoutes(string|Closure $routes): void {
		if ($routes instanceof Closure) {
			$routes($this);
		} else {
			(new RouteFileRegistrar($this))->register($routes);
		}
	}

	/**
	 * Register route
	 *
	 * @param string $uri
	 * @param mixed $action
	 * @return RouteResource
	 */
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
		$routing = new Routing($this->routes());

		return $routing->find();
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
			(array)__FUNCTION__,
			__FUNCTION__,
			null
		);

		$compile = new Compile($rc, $params);

		return $compile->handle();
	}
}