<?php

use Fast\Container;
use Fast\Http\Request;
use Fast\Session\Session;
use Fast\Supports\Response\Response;
use Fast\Http\Exceptions\AppException;

if(!function_exists('app'))
{
	/**
	 * Returns an instance of the Fast\Container class or a specified entity from the container.
	 * This function provides a convenient way to retrieve an instance of the Fast\Container class,
	 * which is a dependency injection container used to manage objects and resolve dependencies in the application.
	 * If no entity is specified, this function returns the singleton instance of the container by calling the 'getInstance()' method of the Fast\Container class.
	 * If an entity is specified as the function parameter,
	 * this function returns the specified entity from the container by calling the 'make()' method of the Fast\Container class and passing the entity name as the parameter.
	 * @param string $entity Optional. The name of the entity to retrieve from the container. Default is an empty string, which returns the container instance.
	 * @return Container|Container::make() An instance of the Fast\Container class or a specified entity from the container.
	 * @throws AppException
	 */
	function app(string $entity = ''){
		if(empty($entity)){
			return Container::getInstance();
		}
		return Container::getInstance()->make($entity);
	}
}

if (!function_exists('config')) {
	/**
	 * Get config setting
	 *
	 * @param string $variable
	 *
	 * @return mixed
	 * @throws AppException
	 */
	function config(string $variable): mixed {
		return app()->make(__FUNCTION__)->getConfig($variable);
	}
}

if (!function_exists('base_path')) {
	/**
	 * Get full path from base
	 *
	 * @param string $path
	 *
	 * @return string
	 * @throws AppException
	 */
	function base_path(string $path = ''): string
	{
		return app()->getBasePath() . ($path ? DIRECTORY_SEPARATOR . $path : $path);
	}
}

if(!function_exists('snake_case')) {
	function snake_case(string $string): string {
		$result = "";
		for($i = 0; $i < strlen($string); $i++) {
			if(ctype_upper($string[$i])) {
				$result .= $i === 0 ? strtolower($string[$i]) : '_' . strtolower($string[$i]);
			} else {
				$result .= strtolower($string[$i]);
			}
		}

		return $result;
	}
}

if(!function_exists('class_name_only')) {
	/**
	 * Get class name only
	 *
	 * @param string $class
	 * @return string
	 */
	function class_name_only(string $class): string
	{
		$explode = explode('\\', $class);

		return end(
			$explode
		);
	}
}

if (!function_exists('request')) {
	/**
	 * Get instance of request
	 *
	 * @return Request
	 * @throws AppException
	 */
	function request(): Request
	{
		return app()->make(__FUNCTION__);
	}
}

if (!function_exists('response')) {
	/**
	 * Make instance of response
	 *
	 * @return Response
	 * @throws AppException
	 */
	function response(): Response
	{
		return app()->make(__FUNCTION__);
	}
}

if (!function_exists('objectToArray')) {
	/**
	 * Convert object to array
	 *
	 * @param \ArrayObject $inputs
	 *
	 * @return array
	 */
	function objectToArray(\ArrayObject $inputs): array
	{
		$array = [];

		foreach ($inputs as $object) {
			$array[] = get_object_vars($object);
		}

		return $array;
	}
}

if (!function_exists('trans')) {
	/**
	 * Get translate value
	 *
	 * @param string $variable
	 * @param array $params
	 * @param string $lang
	 *
	 * @return string
	 * @throws AppException
	 * @throws ReflectionException
	 */
	function trans(string $variable, array $params = [], string $lang = 'en'): string
	{
		return app()->make('translator')->trans($variable, $params, $lang);
	}
}

if (!function_exists('items_in_folder')) {
	/**
	 * Get all items in folder
	 *
	 * @param string $folder
	 * @param bool $included
	 *
	 * @return array
	 */
	function items_in_folder(string $folder, bool $included = true): array
	{
		$dir = new \RecursiveIteratorIterator(
			$folder,
			\FilesystemIterator::SKIP_DOTS
		);

		$iterators = new \RecursiveIteratorIterator(
			$dir,
			\RecursiveIteratorIterator::SELF_FIRST
		);

		$items = [];
		foreach ($iterators as $file_info) {
			if (
				$file_info->isFile()
				&& $file_info !== basename(__FILE__)
				&& $file_info->getFilename() != '.gitignore'
			) {
				$path = !empty($iterators->getSubPath())
					? $iterators->getSubPath() . DIRECTORY_SEPARATOR . $file_info->getFilename()
					: $file_info->getFilename();
				$items[] = ($included ? $folder . DIRECTORY_SEPARATOR : '') . $path;
			}
		}

		return $items;
	}
}

if (!function_exists('session')) {
	/**
	 * Working on session
	 *
	 * @return Session
	 * @throws AppException
	 * @throws ReflectionException
	 */
	function session(): Session
	{
		return app()->make(__FUNCTION__);
	}
}

if (!function_exists('unset_session')) {
	/**
	 * Calling unset_session method in Session
	 *
	 * @param string $key
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
	function unset_session(string $key): void
	{
		app()->make('session')->unset($key);
	}
}

if (!function_exists('route')) {
	/**
	 * Get uri of route from name
	 *
	 * @param string $name
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	function route(string $name): string {
		$routes = app()->make(__FUNCTION__)->collect();
		$flag = false;
		$uri = '';
		foreach ($routes as $key => $route) {
			if (strtolower($name) === strtolower($route->getName())) {
				$flag = true;
				$uri = $route->getUri();
			}
		}
		if ($flag === true) {
			return $uri;
		} else {
			throw new Exception('The route ' . '"' . $name . '"' . " doesn't exists");
		}
	}
}

if (!function_exists('action')) {
	/**
	 * Return action to controller method
	 *
	 * @param mixed $action
	 * @param array $params
	 *
	 * @return mixed
	 * @throws AppException|ReflectionException
	 */
	function action(array $action, array $params = []): mixed {
		return app()->make('route')->callableAction($action, $params);
	}
}

if (!function_exists('__')) {
	/**
	 * Get translate value without params
	 * @throws AppException
	 * @throws ReflectionException
	 */
	function __(string $variable, string $lang = 'en'): string {
		return trans($variable, [], $lang);
	}
}

if (!function_exists('trans')) {
	/**
	 * Get translate value
	 *
	 * @param string $variable
	 * @param array $params
	 * @param string $lang
	 *
	 * @return string
	 * @throws AppException
	 * @throws ReflectionException
	 */
	function trans(string $variable, array $params = [], string $lang = 'en'): string
	{
		return app()->make('translator')->trans($variable, $params, $lang);
	}
}