<?php

use Fast\Http\Exceptions\AppException;

if (!function_exists('view')) {
	/**
	 * Render view file
	 *
	 * @param string $view
	 * @param array $data
	 *
	 * @return mixed
	 * @throws ReflectionException
	 * @throws AppException
	 */
    function view(string $view = '', array $data = []): mixed {
        return $view ? app()->make('view')->render($view, $data) : app()->make('view');
    }
}

if (!function_exists('master')) {
	/**
	 * Set master layout
	 *
	 * @param string $master
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
    function master(string $master): void
    {
        app()->make('view')->setMaster($master);
    }
}

if (!function_exists('setVar')) {
	/**
	 * Set variable for view
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
    function setVar(string $key, mixed $value): void
    {
        app()->make('view')->setVar($key, $value);
    }
}

if (!function_exists('need')) {
	/**
	 * Needed section
	 *
	 * @param string $section
	 * @param string $instead
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
    function need(string $section, string $instead = ''): void
    {
        echo app()->make('view')->getNeedSection($section, $instead);
    }
}

if (!function_exists('section')) {
	/**
	 * Start section
	 *
	 * @param string $section
	 * @param mixed|null $data
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
    function section(string $section, mixed $data = null): void
    {
        if (!is_null($data)) {
            app()->make('view')->setSectionWithData($section, $data);
        } else {
            app()->make('view')->setCurrentSection($section);
        }
    }
}

if (!function_exists('endsection')) {
	/**
	 * End section
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
    function endsection(): void
    {
        app()->make('view')->setDataForSection(ob_get_clean());
    }
}

if (!function_exists('included')) {
	/**
	 * Include partial view
	 *
	 * @param string $path
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
    function included(string $path): void
    {
        $path = str_replace('.', DIRECTORY_SEPARATOR, $path);

        app()->make('view')->makeCache($path);

        $path = cache_path("resources/views/{$path}.php");

        if (file_exists($path)) {
            include $path;
        } else {
            throw new \Fast\Http\Exceptions\AppException("Cache $path not found.");
        }
    }
}
