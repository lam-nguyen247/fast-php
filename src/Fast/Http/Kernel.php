<?php
namespace Fast\Http;

use Closure;
use Fast\Application;
use Fast\Container;
use ReflectionException;
use Route;
use Fast\Pipeline\Pipeline;
use Fast\Http\Exceptions\AppException;
use Fast\Contracts\Http\Kernel as HttpKernel;

class Kernel implements HttpKernel
{
	private Container $app;
	public array $routeMiddlewares = [];

	protected array $middlewares = [];

	/**
	 * @throws ReflectionException
	 * @throws AppException
	 */
	public function __construct()
	{
		$this->app =  Container::getInstance();
		$this->bindingMiddlewares();
		$this->app->singleton(Application::class, function ($app){
			return new Application($app);
		});

		$application = $this->app->make(Application::class);
		$application->run();
	}

	/**
	 * Bindings all middlewares to container
	 *
	 * @return void
	 */
	private function bindingMiddlewares(): void
	{
		foreach ($this->routeMiddlewares as $key => $middleware) {
			$this->app->bind($key, $middleware);
		}
	}

	/**
	 * Handle execute pipeline request
	 * @param Request $request
	 *
	 * @return mixed
	 */
	public function handle(Request $request): mixed {
		return (new Pipeline($this->app))
			->send($request)
			->through($this->middlewares)
			->then($this->dispatchToRouter());
	}

	/**
	 * Handle execute pipeline request
	 * @param Request $request
	 *
	 * @return mixed
	 */
	public function reactHandle(Request $request): mixed {
		Container::getInstance()->instance(Request::class, $request);
		return (new Pipeline($this->app))
			->send($request)
			->through($this->middlewares)
			->then($this->dispatchToRouter());
	}

	/**
	 * Dispatch router of application
	 * @return Closure
	 */
	protected function dispatchToRouter(): Closure
	{
		return function () {
			try{
				$route = new Route;
				return $route->run();
			}catch (AppException $exception){
				return $exception->render($exception);
			}
		};
	}

	/**
	 * Get the application
	 *
	 * @return Container
	 */
	public function getApplication(): Container
	{
		return $this->app;
	}
}
