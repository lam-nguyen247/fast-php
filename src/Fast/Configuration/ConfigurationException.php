<?php

namespace Fast\Configuration;

use Fast\Http\Exceptions\AppException;

class ConfigurationException extends AppException {
	/**
	 * Authentication Configuration Exception constructor
	 *
	 * @param string $message
	 * @param int $code = 400
	 */
	public function __construct(string $message, int $code = 400) {
		parent::__construct($message, $code);
	}
}
