<?php
namespace Fast\Http\Exceptions;

use Fast\Container;
use Fast\Application;
use ReflectionException;
use JetBrains\PhpStorm\NoReturn;
use Fast\Supports\Response\Response;
use Fast\Http\Exceptions\UnknownException;

class ErrorHandler {
	private Container $app;

	/**
	 * @throws AppException|ReflectionException
	 */
	public function __construct() {
		$this->app = Container::getInstance();

		if ($this->app->make(Application::class)->isLoaded()) {
			ob_get_clean();
		}
	}

	/**
	 * Error handler
	 *
	 * @param int $errorNo
	 * @param string $errStr
	 * @param string $file
	 * @param int $line
	 *
	 * @return \React\Http\Message\Response|Response
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function errorHandler(int $errorNo, string $errStr, string $file, int $line): \React\Http\Message\Response|Response {
		$msg = "{$errorNo}: {$errStr} on line {$line} in file {$file}";

		str_replace(base_path(), '', $file);

		$exception = new UnknownException($msg);

		throw $exception->render($exception);
	}
}
