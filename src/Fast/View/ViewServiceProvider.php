<?php

namespace Fast\View;

use Fast\ServiceProvider;

class ViewServiceProvider extends ServiceProvider {
	public function register(): void {
		$this->app->singleton('view', function () {
			$directory = base_path('resources/views');
			$cacheDirectory = cache_path('resources/views');
			return new \Fast\View\View($directory, $cacheDirectory);
		});
	}
}
