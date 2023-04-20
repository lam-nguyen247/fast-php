<?php

namespace Fast\Storage;

use Fast\ServiceProvider;
use Fast\Http\Exceptions\AppException;

class StorageServiceProvider extends ServiceProvider
{
	/**
	 * @throws \ReflectionException
	 * @throws AppException
	 */
	public function boot(): void
    {
        $storage = $this->app->make('storage');

        $storage->disk(config('storage.default'));
    }

    public function register(): void
    {
        $this->app->singleton('storage', function () {
            return new \Fast\Storage\Storage;
        });
    }
}
