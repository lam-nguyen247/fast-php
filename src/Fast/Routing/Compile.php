<?php
namespace Fast\Routing;

use Fast\Container;
use Fast\Hashing\HashException;
use Fast\Eloquent\EloquentException;
use Fast\Http\Exceptions\AppException;
use Fast\Auth\AuthenticationException;
use Fast\Translator\TranslationException;
use Fast\Http\Exceptions\UnknownException;
use Fast\Http\Validation\ValidationException;
use Fast\Database\QueryBuilder\QueryException;
use Fast\Configuration\ConfigurationException;
use Fast\Http\Exceptions\UnauthorizedException;
use Fast\Database\DatabaseBuilder\DatabaseBuilderException;
use Fast\Database\Connections\Mysql\MysqlConnectionException;
use Fast\Database\Connections\PostgresSQL\PostgresConnectionException;

class Compile
{
	private string $method;

	private string $controller;

	private RouteCollection $route;

	private array $params = [];

	const DEFAULT_SPECIFIC = '@';

	const __INVOKE = '__invoke';

	private Container $app;

	/**
	 * @throws RouteException
	 */
	public function __construct(RouteCollection $route, array $params)
	{
		$this->makeRoute($route);
		$this->makeParams($params);

		$this->findingTarget(
			$this->getAction()
		);

		$this->app = Container::getInstance();
	}

	/**
	 * @throws RouteException
	 * @throws UnknownException
	 * @throws AppException
	 */
	public function handle(){
		$controller = $this->getFullNameSpace($this->getController());
		$method = $this->getMethod();

		if(!class_exists($controller) || !method_exists($controller, $method)) {
			throw new RouteException("Endpoint target '{$controller}@{$method}' does not exists");
		}
		try {
			$object = $this->app->build($controller);
			$params = $this->app->resolveMethodDependencyWithParameters($controller, $method, $this->getParams());
			return call_user_func_array([$object, $method], $params);
		} catch (\Exception $e) {
			throw match (true) {
				$e instanceof AppException,
				$e instanceof HashException,
				$e instanceof ViewException,
				$e instanceof RouteException,
				$e instanceof QueryException,
				$e instanceof LoggerException,
				$e instanceof RuntimeException,
				$e instanceof ConsoleException,
				$e instanceof StorageException,
				$e instanceof EloquentException,
				$e instanceof DispatcherException,
				$e instanceof FileSystemException,
				$e instanceof MiddlewareException,
				$e instanceof ValidationException,
				$e instanceof TranslationException,
				$e instanceof UnauthorizedException,
				$e instanceof ConfigurationException,
				$e instanceof AuthenticationException,
				$e instanceof DatabaseBuilderException,
				$e instanceof MysqlConnectionException,
				$e instanceof PostgresConnectionException => $e,
				default => new UnknownException($e->getMessage()),
			};
		}
	}

	/**
	 * Find the target controller and method
	 *
	 * @param array|string $action
	 *
	 * @return void
	 */
	private function findingTarget(array|string $action): void
	{
		[$controller, $method] = is_array($action)
			? (count($action) === 1
				? [array_shift($action), Compile::__INVOKE]
				: $action)
			: (count(explode(Compile::DEFAULT_SPECIFIC, $action)) === 1
				? [$action, Compile::__INVOKE]
				: explode(Compile::DEFAULT_SPECIFIC, $action));

		$this->setMethod($method);
		$this->setController($controller);
	}

	private function setMethod(string $method): void
	{
		$this->method = $method;
	}

	private function getMethod(): string
	{
		return $this->method;
	}

	private function setController(string $controller): void
	{
		$this->controller = $controller;
	}

	private function getController(): string
	{
		return $this->controller;
	}

	private function makeRoute(RouteCollection $route): void
	{
		$this->route = $route;
	}

	private function getParams(): array
	{
		return $this->params;
	}

	private function getRoute(): RouteCollection
	{
		return $this->route;
	}

	/**
	 * Get action value
	 *
	 * @return array|string
	 * @throws RouteException
	 */
	private function getAction(): array|string {
		$action = $this->getRoute()->{__FUNCTION__}();
		if (empty($action)) {
			$name = $this->getRoute()->getName();
			if (empty($name)) {
				$name = $this->getRoute()->getUri();
			}
			throw new RouteException('Routing is matched ! But missing action. Please set the action.');
		}

		return $action;
	}

	private function getFullNamespace(string $controller): string
	{
		$namespace = $this->getRoute()->getNamespace();

		return !empty($namespace)
			? implode("\\", $namespace) . "\\" . $controller
			: $controller;
	}

	private function makeParams(array $params): void
	{
		$this->params = $params;
	}
}