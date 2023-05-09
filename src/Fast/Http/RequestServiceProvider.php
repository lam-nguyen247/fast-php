<?php
namespace Fast\Http;

use Fast\ServiceProvider;

class RequestServiceProvider extends ServiceProvider {
	/**
	 * Register all the service providers that you
	 * import in config/app.php -> providers
	 *
	 * @return void
	 */
	public function register(): void {
		$this->app->singleton('request', function () {
			return new Request;
		});
	}
}