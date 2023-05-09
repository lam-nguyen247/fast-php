<?php

namespace Fast\Database\Connections\PostgresSQL;

use Fast\Http\Exceptions\AppException;

class PostgresConnectionException extends AppException {
	/**
	 * AuthenticationException constructor
	 *
	 * @param string $message
	 * @param int $code = 400
	 */
	public function __construct(string $message, int $code = 400) {
		parent::__construct($message, $code);
	}
}
