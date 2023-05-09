<?php

namespace Fast\Hashing;

use Fast\ServiceProvider;
use Fast\Hashing\BcryptHashes;

class HashServiceProvider extends ServiceProvider {
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->app->singleton('hash', function () {
			return new BcryptHashes;
		});
	}
}
