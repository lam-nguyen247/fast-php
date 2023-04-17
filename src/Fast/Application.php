<?php
namespace Fast;

use Fast\Traits\Instance;
use Fast\Http\Exceptions\ErrorHandler;
use Fast\Http\Exceptions\AppException;
use Fast\Http\Exceptions\RuntimeException;

class Application
{
	use Instance;

	private Container $container;

	private Config $config;

	private bool $loaded = false;

	/**
	 * @throws AppException
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->registerConfigProvider();

		new AliasLoader();

		register_shutdown_function([$this, 'whenShutDown']);

		$this->setErrorHandler();
	}

	/**
	 * Register initial configuration providerisLoaded
	 *
	 * @return void
	 */
	private function registerConfigProvider(): void
	{
		$this->container->singleton('config', function () {
			return new \Fast\Configuration\Config();
		});
	}

	/**
	 * Set error handler
	 *
	 * @return void
	 * @throws AppException
	 */
	public function setErrorHandler(): void {
		set_error_handler(function () {
			$handler = new ErrorHandler;

			return $handler->errorHandler(...func_get_args());
		});
	}

	/**
	 * Get status load provider
	 *
	 * @return bool
	 */
	public function isLoaded(): bool
	{
		return $this->loaded;
	}

	/**
	 * Register service providers
	 * @throws \ReflectionException
	 * @throws AppException
	 */
	public function registerServiceProvider(): void
	{
		$providers = $this->container->make('config')->getConfig('app>providers');

		if(!empty($providers)) {
			foreach ($providers as $provider) {
				$provider = new $provider;
				$provider->register();
			}

			foreach ($providers as $provider) {
				$provider = new $provider;
				$provider->boot();
			}
		}
	}

	/**
	 * Set state load provider
	 *
	 * @param bool $isLoad
	 *
	 * @return void
	 */
	public function setLoadState(bool $isLoad): void {
		$this->loaded = $isLoad;
	}

	/**
	 * @throws \ReflectionException
	 * @throws AppException
	 */
	public function loadConfiguration(): void{
		$cache = array_filter(scandir(cache_path()), function ($item) {
			return str_contains($item, '.php');
		});
		foreach ($cache as $item) {
			$key = str_replace('.php', '', $item);

			$value = require cache_path($item);

			$this->container->make('config')->setConfig($key, $value);
		}
	}

	/**
	 * Run the application
	 *
	 * @return void
	 * @throws AppException
	 * @throws \ReflectionException
	 */
	public function run(): void
	{
		$this->loadConfiguration();
		$this->registerServiceProvider();
		$this->setLoadState(true);
	}

	/**
	 * Terminate the application
	 */
	public function terminate(): void
	{ }

	/**
	 * Set error handler
	 *
	 * @return void
	 *
	 * @throws RuntimeException
	 * @throws AppException
	 */
	public function whenShutDown(): void
	{
		$last_error = error_get_last();
		if (!is_null($last_error)) {
			ob_clean();
			$handler = new ErrorHandler();

			$handler->errorHandler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
		}
	}
}