<?php
namespace Fast\Routing;

class RouteCollection {
	private string $methods;

	private string $uri;

	private mixed $action;

	private string $name;

	private array $middlewares = [];

	private array $prefix = [];

	private array $namespaces = [];

	/**
	 * Initial constructor
	 *
	 * @param string $methods
	 * @param string $uri
	 * @param string $name
	 * @param mixed $action
	 * @param array $middlewares
	 * @param mixed $prefix
	 * @param mixed $namespaces
	 *
	 */
	public function __construct(string $methods,
								string $uri,
								string $name,
								mixed  $action,
								array  $middlewares,
								mixed  $prefix,
								mixed  $namespaces) {
		$this->methods = $methods;
		$this->uri = $uri;
		$this->name = $name;
		$this->action = $action;
		$this->middlewares = $middlewares;
		$this->prefix = is_array($prefix) && !empty($prefix) ? $prefix : [$prefix];
		$this->namespaces = is_array($namespaces) && !empty($namespaces) ? $namespaces : [$namespaces];
	}

	/**
	 * Set middleware
	 *
	 * @param array|string $middleware
	 *
	 * @return self
	 */
	public function middleware(array|string $middleware): RouteCollection {
		if (!is_array($middleware)) {
			$this->middlewares[] = $middleware;
		} else {
			$this->middlewares = array_merge($this->middlewares, $middleware);
		}
		return $this;
	}

	/**
	 * Set namespace
	 *
	 * @param array|string $namespace
	 *
	 * @return self
	 */
	public function namespace(array|string $namespace): RouteCollection {
		$this->namespaces[] = $namespace;
		return $this;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return self
	 */
	public function name(string $name): RouteCollection {
		$this->name .= $name;
		return $this;
	}

	/**
	 * Set prefix
	 *
	 * @param string $prefix
	 *
	 * @return self
	 */
	public function prefix(string $prefix): RouteCollection {
		$this->prefix[] = $prefix;
		return $this;
	}

	/**
	 * Get uri
	 *
	 * @return string
	 */
	public function getUri(): string {
		return empty($this->uri)
		|| $this->uri[0]
		!= Routing::ROUTING_SEPARATOR
			? Routing::ROUTING_SEPARATOR . $this->uri
			: $this->uri;
	}

	public function getMethods(): string {
		return $this->methods;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getAction(): mixed {
		return $this->action;
	}

	public function getMiddlewares(): array {
		return $this->middlewares;
	}

	public function getPrefix(): array {
		return !empty($this->prefix && !empty($this->prefix[0])) ? $this->prefix : [];
	}

	public function getNamespace(): array {
		return !empty($this->namespaces && !empty($this->namespaces[0])) ? $this->namespaces : [];
	}
}
