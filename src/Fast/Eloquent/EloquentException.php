<?php

namespace Fast\Eloquent;

use Fast\Http\Exceptions\AppException;

class EloquentException extends AppException {
	/**
	 * AuthenticationException constructor
	 *
	 * @param string $message
	 * @param int $code = 400
	 * @throws AppException
	 * @throws \ReflectionException
	 */
	public function __construct(string $message, int $code = 400) {
		parent::__construct($message, $code);
	}
}
