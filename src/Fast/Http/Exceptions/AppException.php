<?php

namespace Fast\Http\Exceptions;

use Exception;
use Fast\Application;
use Throwable;
use ReflectionException;

class AppException extends Exception
{
	/**
	 * @throws ReflectionException
	 * @throws AppException
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		$this->writeLog($message);

		parent::__construct($message, $code, $previous);

		set_exception_handler([$this, 'render']);
	}

	/**
	 * @throws ReflectionException
	 * @throws AppException
	 */
	public function render(Exception $exception): \Fast\Supports\Response\Response|\React\Http\Message\Response {
		return response()->json([
			'status'  => false,
			'message' => $exception->getMessage()
		], $this->code);
	}

	/**
	 * @throws ReflectionException
	 * @throws AppException
	 */
	public function writeLog(string $message): void
	{
		if(app()->make(Application::class)->isLoaded()) {
			app()->make('log')->error(
				(new \ReflectionClass(static::class))
					->getShortName(). "throws $message from". $this->getFile(). 'line '. $this->getLine()
			);
		}
	}
}