<?php
namespace Fast;

use Fast\Traits\Instance;
use Fast\Http\Exceptions\ErrorHandler;

class Application
{
	use Instance;

	private Container $container;

	private Config $config;

	private bool $loaded = false;

	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->registerConfigProvider();

		new AliasLoader();

		register_shutdown_function([$this, 'whenShutDown']);
	}

	/**
	 * Register initial configuration provider
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
	 * @return mixed
	 */
	public function setErrorHandler(): mixed {
		set_error_handler(function () {
			$handler = new ErrorHandler;

			return $handler->errorHandler(...func_get_args());
		});
	}
}