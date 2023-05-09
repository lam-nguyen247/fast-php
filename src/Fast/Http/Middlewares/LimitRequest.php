<?php

namespace Fast\Http\Middlewares;

use Closure;
use Fast\Container;
use Fast\Http\Request;
use Fast\Http\Middlewares\MiddlewareException;

class LimitRequest {
	/**
	 * The application implementation.
	 *
	 * @var Container
	 */
	protected Container $app;

	/**
	 * Name of attempts
	 *
	 * @var string
	 */
	const ATTEMPTS = 'attempts';
	private \Fast\Session\Session $session;

	/**
	 * Create a new middleware instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->app = Container::getInstance();
		$this->session = new \Fast\Session\Session();

		$id = session_id();

		if (!$this->session->isset($id)) {
			$this->createNewClient($id);
		}
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param Request $request
	 * @param Closure $next
	 * @param int $max
	 * @param int $seconds
	 * @param int $waits
	 * @return mixed
	 *
	 * @throws MiddlewareException
	 */
	public function handle(Request $request, Closure $next, int $max = 60, int $seconds = 60, int $waits = 10): mixed {
		$id = session_id();
		$session = $this->session->get($id);

		if (time() - $session['start_time'] > $seconds) {
			$this->session->unset($id);
			$this->createNewClient($id);
			$session = $this->session->get($id);
		}

		if ($session[LimitRequest::ATTEMPTS] > $max) {
			if (time() - $session['last_time'] > $waits) {
				$this->session->unset($id);
				$this->createNewClient($id);
				$session = $this->session->get($id);
			} else {
				throw new MiddlewareException("You're request too many times. Please wait.");
			}
		}

		$this->session->set($id, [
			LimitRequest::ATTEMPTS => $session[LimitRequest::ATTEMPTS] + 1,
			'start_time' => $session['start_time'],
			'last_time' => time(),
		]);

		return $next($request);
	}

	/**
	 * Clear new client with session id
	 *
	 * @param string $id
	 *
	 * @return void
	 */
	private function createNewClient(string $id): void {
		$this->session->set($id, [
			LimitRequest::ATTEMPTS => 0,
			'start_time' => time(),
			'last_time' => time(),
		]);
	}
}
