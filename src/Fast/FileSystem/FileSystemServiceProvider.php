<?php

namespace Fast\FileSystem;

use Fast\ServiceProvider;

class FileSystemServiceProvider extends ServiceProvider {
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->app->singleton('fileSystem', function () {
			return new FileSystem();
		});
	}
}
