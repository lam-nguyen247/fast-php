<?php

namespace Fast\Routing;

use Throwable;
use Fast\Http\Exceptions\AppException;

class RouteException extends AppException
{
	public function __construct($message = "", $code = 404, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
