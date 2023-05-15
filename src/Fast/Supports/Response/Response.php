<?php

namespace Fast\Supports\Response;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse {

	function __construct(?string $content = '', int $status = 200, array $headers = ['Content-Type' => 'application/json']) {
		parent::__construct($content, $status, $headers);
	}

	/**
	 * Response with json
	 *
	 * @param mixed $arguments
	 * @param int $code = 200
	 *
	 * @return Response
	 */
	public final function json(mixed $arguments, int $code = 200): Response {
		if(is_array($arguments) || is_object($arguments)) {
			$arguments = json_encode($arguments);
		}
		parent::__construct($arguments, $code, ['Content-Type' => 'application/json']);
		return $this;
	}
}
