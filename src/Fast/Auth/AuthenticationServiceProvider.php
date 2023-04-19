<?php

namespace Fast\Auth;

use Fast\ServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider
{
	/**
	 * Register all the service providers that you
	 * import in config/app.php -> providers
	 *
	 * @return void
	 */
	public function register(): void
	{
		$this->app->singleton('auth', function () {
			return new Authenticatable;
		});
	}
}
