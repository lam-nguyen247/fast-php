<?php

namespace Fast\FileSystem;

use Fast\Http\Exceptions\AppException;

class FileSystemException extends AppException {
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
