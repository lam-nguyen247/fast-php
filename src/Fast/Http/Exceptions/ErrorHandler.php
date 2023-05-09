<?php
namespace Fast\Http\Exceptions;

use Fast\Container;
use Fast\Application;
use ReflectionException;
use JetBrains\PhpStorm\NoReturn;
use Fast\Http\Exceptions\UnknownException;

class ErrorHandler {
	private Container $app;

	/**
	 * @throws AppException|ReflectionException
	 */
	public function __construct() {
		$this->app = Container::getInstance();

		if ($this->app->make(Application::class)->isLoaded()) {
			$this->app->make('view')->setMaster('');
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
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function errorHandler(int $errorNo, string $errStr, string $file, int $line): void {
		$msg = "{$errStr} on line {$line} in file {$file}";

		$file = str_replace(base_path(), '', $file);

		$exception = new UnknownException($msg);

		$exception->render($exception);
		exit($errorNo);
	}
}