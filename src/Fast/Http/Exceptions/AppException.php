<?php

namespace Fast\Http\Exceptions;

use Exception;
use Fast\Application;
use Throwable;

class AppException extends Exception
{
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	private function writeLog(string $message): void
	{
		if(app()->make(Application::class)->isLoaded()) {

		}
	}

	public function render(\Exception $exception){
		if(request()->isAjax()) {
			return response()->json([]);
		}
	}
}