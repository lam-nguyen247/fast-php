<?php

namespace Fast\Auth;

use ReflectionException;
use Fast\Http\Exceptions\AppException;

class AuthenticationException extends AppException {
	/**
	 * AuthenticationException constructor
	 *
	 * @param string $message
	 * @param int $code = 400
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function __construct(string $message, int $code = 401) {
		parent::__construct($message, $code);
	}
}
