<?php

namespace Fast\Http\Middlewares;

use Closure;
use Fast\Container;
use Fast\Http\Request;
use Fast\Http\Exceptions\AppException;
use Fast\Http\Middlewares\MiddlewareException;

class CheckIsMaintenanceMode {
	/**
	 * The application implementation.
	 *
	 * @var Container
	 */
	protected Container $app;

	/**
	 * Create a new middleware instance.
	 *
	 * @return void
	 * @throws AppException
	 */
	public function __construct() {
		$this->app = app();
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param Request $request
	 * @param Closure $next
	 * @return mixed
	 *
	 */
	public function handle(Request $request, Closure $next): mixed {
		if ($this->app->isDownForMaintenance()) {
			die("This application is down for maintenance");
		}

		return $next($request);
	}
}
