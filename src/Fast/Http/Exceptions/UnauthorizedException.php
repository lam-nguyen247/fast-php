<?php
namespace Fast\Http\Exceptions;

use Throwable;

class UnauthorizedException extends AppException {
	public function __construct($message = "Unauthorized !", $code = 401, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}