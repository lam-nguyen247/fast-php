<?php

namespace Fast\Routing\Controller;

use Fast\ServiceProvider;

class ControllerServiceProvider extends ServiceProvider
{
    /**
     * Register all the service providers that you
     * import in config/app.php -> providers
     * 
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('controller', function () {
            return new \Fast\Routing\Controller\Controller;
        });
    }
}
