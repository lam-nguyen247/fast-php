<?php

namespace Fast\Http\Validation;

use ReflectionException;
use Fast\ServiceProvider;
use Fast\Http\Validation\Validator;
use Fast\Http\Exceptions\AppException;

class ValidationServiceProvider extends ServiceProvider
{
	/**
	 * Run after the application already registered service,
	 * if you want to use 3rd or outside service,
	 * please implement them to the boot method.
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
    public function boot(): void
    {
        $validator = $this->app->make('validator');
        $validator->setRules([
            'required',
            'min',
            'max',
            'number',
            'string',
            'file',
            'image',
            'video',
            'audio',
            'email',
            'unique'
        ]);
    }

    /**
     * Register all the service providers that you
     * import in config/app.php -> providers
     * 
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('validator', function () {
            return new Validator();
        });
    }
}
