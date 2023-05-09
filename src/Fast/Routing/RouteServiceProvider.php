<?php

namespace Fast\Routing;

use Fast\ServiceProvider;

class RouteServiceProvider extends ServiceProvider {
	/**
	 * Register singleton routing
	 */
	public function register(): void {
		$this->app->singleton('route', function () {
			return new Router;
		});
	}
}
