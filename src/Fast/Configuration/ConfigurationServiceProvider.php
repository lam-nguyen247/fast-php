<?php

namespace Fast\Configuration;

use Fast\ServiceProvider;
use Fast\Http\Exceptions\AppException;

class ConfigurationServiceProvider extends ServiceProvider {
	/**
	 * Register 3rd-party services
	 * @throws AppException
	 */
	public function boot(): void {
		date_default_timezone_set(config('app.timezone'));
	}

	/**
	 * Register all the service providers that you
	 * import in config/app.php -> providers
	 *
	 * @return void
	 */
	public function register(): void {
		$this->app->singleton('config', function () {
			return $this->app->make('config');
		});
	}
}
