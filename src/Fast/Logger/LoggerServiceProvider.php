<?php

namespace Fast\Logger;

use ReflectionException;
use Fast\ServiceProvider;
use Fast\Http\Exceptions\AppException;

class LoggerServiceProvider extends ServiceProvider
{
    /**
     * Booting
	 * @throws AppException|ReflectionException
	 */
    public function boot(): void
    {
        $logger = $this->app->make('log');

        $logger->setDirectory(config('logger.directory'));

        $logger->setWriteLogByDate(config('logger.by_date'));
    }

    /**
     * Register singleton routing
     */
    public function register(): void
    {
        $this->app->singleton('log', function () {
            return new \Fast\Logger\Logger();
        });
    }
}
