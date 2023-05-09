<?php
namespace Fast\Routing;

use Fast\Traits\Routing\Resource;

class RouteResource {
	use Resource;

	private string $name;

	private array $middlewares = [];

	private array $prefix = [];

	private array $namespaces = [];

	private array $resources = [];

	private array $except = [];

	public function __construct() {
		[$resource, $name, $middlewares, $prefix, $namespaces] = func_get_args();

		$this->middlewares = is_array($middlewares) ? $middlewares : [$middlewares];
		$this->prefix = is_array($prefix) ? $prefix : [$prefix];
		$this->namespaces = is_array($namespaces) ? $namespaces : [$namespaces];
		$this->name = $name;
		$this->resources = is_array($resource) ? $resource : [$resource];
	}

	public function except(array $methods): RouteResource {
		$this->except = $methods;
		return $this;
	}

	public function middleware(mixed $middleware): RouteResource {
		if (!is_array($middleware)) {
			$this->middlewares[] = $middleware;
		} else {
			$this->middlewares = array_merge($this->middlewares, $middleware);
		}
		return $this;
	}

	public function namespace(string $namespace): RouteResource {
		$this->namespaces[] = $namespace;
		return $this;
	}

	public function name(string $name): RouteResource {
		$this->name .= $name;
		return $this;
	}

	public function prefix(string $prefix): RouteResource {
		$this->prefix[] = $prefix;
		return $this;
	}

	/**
	 * Parse list resource to RouteCollections
	 *
	 * @return array
	 */
	public function parse(): array {
		$routes = [];

		foreach ($this->resources as $resource) {
			$routes[] = $this->makeIndex($resource);
			$routes[] = $this->makeCreate($resource);
			$routes[] = $this->makeShow($resource);
			$routes[] = $this->makeStore($resource);
			$routes[] = $this->makeEdit($resource);
			$routes[] = $this->makeUpdate($resource);
			$routes[] = $this->makeDelete($resource);
		}

		return array_filter($routes, fn($route) => !is_null($route));
	}
}