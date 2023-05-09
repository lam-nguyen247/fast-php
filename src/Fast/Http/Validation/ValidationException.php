<?php
namespace Fast\Http\Validation;

use Throwable;
use Fast\Http\Exceptions\AppException;

class ValidationException extends AppException {
	public function __construct($message = "", $code = 400, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}