<?php

namespace Fast\Traits\Response;

use ReflectionException;
use Fast\Supports\Response\Response;
use Fast\Http\Exceptions\AppException;

trait JsonResponse {
	/**
	 * Return generic json response with the given data.
	 *
	 * @param mixed $data
	 * @param int $statusCode
	 * @return Response
	 * @throws AppException
	 * @throws ReflectionException
	 */
	protected function respond(mixed $data, int $statusCode = 200): Response {
		return response()->json($data, $statusCode);
	}

	/**
	 * Respond with created.
	 *
	 * @param mixed $data
	 * @return Response
	 * @throws AppException|ReflectionException
	 */
	protected function respondCreated(mixed $data): Response {
		return $this->respond($data, 201);
	}

	/**
	 * Respond with success.
	 *
	 * @param mixed $data
	 * @param int $statusCode
	 * @return Response
	 * @throws AppException|ReflectionException
	 */
	protected function respondSuccess(mixed $data, int $statusCode = 200): Response {
		return $this->respond([
			'success' => true,
			'data' => $data,

		], $statusCode);
	}

	/**
	 * Respond with error.
	 *
	 * @param string $message
	 * @param int $statusCode
	 * @return Response
	 * @throws AppException|ReflectionException
	 */
	protected function respondError(string $message = 'Bad request', int $statusCode = 400): Response {
		return $this->respond([
			'success' => false,
			'errors' => [
				'message' => $message,
			],
		], $statusCode);
	}

	/**
	 * Respond with no content.
	 *
	 * @return Response
	 * @throws AppException|ReflectionException
	 */
	protected function respondNoContent(): Response {
		return $this->respondSuccess(null, 204);
	}

	/**
	 * Respond with unauthorized.
	 *
	 * @param string $message
	 * @return Response
	 * @throws AppException|ReflectionException
	 */
	protected function respondUnauthorized(string $message = 'Unauthorized'): Response {
		return $this->respondError($message, 401);
	}

	/**
	 * Respond with forbidden.
	 *
	 * @param string $message
	 * @return Response
	 * @throws AppException|ReflectionException
	 */
	protected function respondForbidden(string $message = 'Forbidden'): Response {
		return $this->respondError($message, 403);
	}

	/**
	 * Respond with not found.
	 *
	 * @param string $message
	 * @return Response
	 * @throws AppException|ReflectionException
	 */
	protected function respondNotFound(string $message = 'Not Found'): Response {
		return $this->respondError($message, 404);
	}

	/**
	 * Respond with internal error.
	 *
	 * @param string $message
	 * @return Response
	 * @throws AppException|ReflectionException
	 */
	protected function respondInternalError(string $message = 'Internal Error'): Response {
		return $this->respondError($message, 500);
	}
}
