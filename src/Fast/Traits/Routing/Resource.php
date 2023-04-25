<?php
namespace Fast\Traits\Routing;

use Fast\Routing\RouteCollection;

trait Resource
{
	/**
	 * Make route index
	 *
	 * @param array $resources
	 * @return RouteCollection|null
	 */
	public function makeIndex(array $resources): ?RouteCollection
	{
		if(!in_array('index', $this->except)) {
			return $this->createRoute('GET', "/{$resources['uri']}", $this->name . "{$resources['uri']}.index", "{$resources['action']}@index", $this->middlewares, $this->prefix, $this->namespaces);
		}
		return null;
	}

	/**
	 * Make route create
	 *
	 * @param array $resources
	 *
	 * @return RouteCollection|null
	 */
	public function makeCreate(array $resources): ?RouteCollection
	{
		if (!in_array('create', $this->except)) {
			return $this->createRoute(
				'GET',
				"/{$resources['uri']}/create",
				$this->name . "{$resources['uri']}.create",
				"{$resources['action']}@create",
				$this->middlewares,
				$this->prefix,
				$this->namespaces
			);
		}

		return null;
	}

	/**
	 * Make route shows
	 *
	 * @param array $resources
	 *
	 * @return RouteCollection|null
	 */
	public function makeShow(array $resources): ?RouteCollection
	{
		if (!in_array('show', $this->except)) {
			return $this->createRoute(
				'GET',
				"/{$resources['uri']}" . '/' . '{' . $resources['uri'] . '}',
				$this->name . "{$resources['uri']}.show",
				"{$resources['action']}@show",
				$this->middlewares,
				$this->prefix,
				$this->namespaces
			);
		}

		return null;
	}

	/**
	 * Make route store
	 *
	 * @param array $resources
	 *
	 * @return RouteCollection
	 */
	public function makeStore(array $resources): ?RouteCollection
	{
		if (!in_array('store', $this->except)) {
			return $this->createRoute(
				'POST',
				"/{$resources['uri']}",
				$this->name . "{$resources['uri']}.store",
				"{$resources['action']}@store",
				$this->middlewares,
				$this->prefix,
				$this->namespaces
			);
		}

		return null;
	}

	/**
	 * Make route edit
	 *
	 * @param array $resources
	 *
	 * @return RouteCollection|null
	 */
	public function makeEdit(array $resources): ?RouteCollection
	{
		if (!in_array('edit', $this->except)) {
			return $this->createRoute(
				'GET',
				"/{$resources['uri']}/{{$resources['uri']}}/edit",
				$this->name . "{$resources['uri']}.edit",
				"{$resources['action']}@edit",
				$this->middlewares,
				$this->prefix,
				$this->namespaces
			);
		}

		return null;
	}

	/**
	 * Make route update
	 *
	 * @param array $resources
	 *
	 * @return RouteCollection
	 */
	public function makeUpdate(array $resources): ?RouteCollection
	{
		if (!in_array('update', $this->except)) {
			return $this->createRoute(
				'PUT',
				"/{$resources['uri']}/{{$resources['uri']}}",
				$this->name . "{$resources['uri']}.update",
				"{$resources['action']}@update",
				$this->middlewares,
				$this->prefix,
				$this->namespaces
			);
		}

		return null;
	}

	/**
	 * Make route delete
	 *
	 * @param array $resources
	 *
	 * @return RouteCollection|null
	 */
	public function makeDelete(array $resources): ?RouteCollection
	{
		if (!in_array('destroy', $this->except)) {
			return $this->createRoute(
				'DELETE',
				"/{$resources['uri']}/{{$resources['uri']}}",
				$this->name . "{$resources['uri']}.destroy",
				"{$resources['action']}@destroy",
				$this->middlewares,
				$this->prefix,
				$this->namespaces
			);
		}

		return null;
	}

	/**
	 * Create RouteCollection
	 *
	 * @param string $methods
	 * @param string $uri
	 * @param string $name
	 * @param mixed $action
	 * @param array $middlewares
	 * @param array $prefix
	 * @param array $namespace
	 *
	 * @return RouteCollection|null
	 */
	public function createRoute(string $methods, string $uri, string $name, mixed $action, array $middlewares, array $prefix, array $namespace): ?RouteCollection
	{
		return new RouteCollection($methods, $uri, $name, $action, $middlewares, $prefix, $namespace);
	}
}