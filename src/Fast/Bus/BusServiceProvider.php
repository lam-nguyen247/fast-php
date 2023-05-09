<?php

namespace Fast\Bus;

use Fast\ServiceProvider;

class BusServiceProvider extends ServiceProvider {
	/**
	 * Register all the service providers that you
	 * import in config/app.php -> providers
	 *
	 * @return void
	 */
	public function register(): void {
		$this->app->bind(\Fast\Contracts\Bus\Dispatcher::class, \Fast\Bus\Dispatcher::class);
	}
}
