<?php
namespace Fast\Translator;

use Fast\ServiceProvider;
use Fast\Http\Exceptions\AppException;

class TranslatorServiceProvider extends ServiceProvider
{
	/**
	 * @throws AppException
	 * @throws \ReflectionException
	 */
	public function boot(): void
	{
		$caches = items_in_folder('resources/lang', false);

		foreach ($caches as $cache){
			$key = str_replace('.php', '', $cache);
			$value = require base_path("resources/lang/{$cache}");
			$this->app->make('translator')->setTranslation($key, $value);
		}
	}

	public function register(): void {
		$this->app->singleton('translator', function (){
			return $this->app->make(Translator::class);
		});
	}
}