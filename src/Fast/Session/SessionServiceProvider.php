<?php

namespace Fast\Session;

use Fast\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register all the service providers that you
     * import in config/app.php -> providers
     * 
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('session', function () {
            return new \Fast\Session\Session;
        });
    }
}
