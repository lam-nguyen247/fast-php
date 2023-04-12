<?php
namespace Fast\Http\Exceptions;

use Fast\Container;
use Fast\Application;
use Fast\Http\Exceptions\UnknownException;

class ErrorHandler
{
	private Container $app;

	/**
	 * @throws AppException
	 */
	public function __construct()
	{
		$this->app = Container::getInstance();

		if($this->app->make(Application::class)->isLoaded())
		{
			$this->app->make('view')->setMaster('');
			ob_get_clean();
		}
	}

	public function errorHandler(int $errorNo, string $errStr, string $file, int $line): void
	{
		$msg = "{$errorNo} on line {$line} in file {$file}";

		$file = str_replace(base_path(), '', $file);

		$exception = new UnknownException($msg);

		$exception->render()
	}
}