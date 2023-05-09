<?php

namespace Fast\Supports\Response;

use Fast\ServiceProvider;

class DataResponseServiceProvider extends ServiceProvider {
	/**
	 * Register all the service providers that you
	 * import in config/app.php -> providers
	 *
	 * @return void
	 */
	public function register(): void {
		$this->app->singleton('response', function () {
			return new \Fast\Supports\Response\Response;
		});
	}
}
