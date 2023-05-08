<?php

namespace Fast\Http\Exceptions;

use ReflectionException;
use Fast\Http\Exceptions\AppException;

class UnknownException extends AppException
{
	/**
	 * AuthenticationException constructor
	 *
	 * @param string $message
	 * @param int $code = 400
	 * @throws AppException
	 * @throws ReflectionException
	 */
    public function __construct($message = 'Unknown !', $code = 400)
    {
        parent::__construct($message, $code);
    }
}
