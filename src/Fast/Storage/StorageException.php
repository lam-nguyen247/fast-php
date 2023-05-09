<?php

namespace Fast\Storage;

use Throwable;
use Fast\Http\Exceptions\AppException;

class StorageException extends AppException {
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
