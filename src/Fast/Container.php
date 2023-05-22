<?php
namespace Fast;

use Closure;
use ReflectionException;
use Fast\Eloquent\Model;
use Fast\Http\FormRequest;
use Fast\Http\Exceptions\AppException;

class Container {
	/**
	 * Version of the application
	 *
	 * @const  VERSION
	 */
	const VERSION = '1.0.0';

	/**
	 * Instance of the application
	 * @var self $instance
	 */
	private static self $instance;

	/**
	 * List of bindings instances
	 *
	 * @var array $instances
	 */
	private array $instances = [];

	/**
	 * Base path of the installation
	 * @var string $basePath
	 */
	private string $basePath;

	/**
	 * Storage saving registry variables
	 * @var array $storage
	 */
	private array $storage = [];

	/**
	 * Storage saving bindings objects
	 * @var array $bindings
	 */
	private array $bindings = [];

	/**
	 * List of resolved bindings
	 * @var array $resolves
	 */
	private array $resolves = [];

	/**
	 * Flag check skip middleware
	 * @var bool $skipMiddleware
	 */
	private bool $skipMiddleware = false;

	/**
	 * Initial of container
	 *
	 * @param string $basePath
	 */
	public function __construct(string $basePath) {
		$this->basePath = $basePath;

		$this->instance('path.route', $this->getRoutePath());
		$this->instance('path.cache', $this->getCachePath());
		$this->instance('path.config', $this->getConfigPath());
		$this->instance('path.public', $this->getPublicPath());
		$this->instance('path.storage', $this->getStoragePath());
		$this->instance('path.database', $this->getDatabasePath());

		self::$instance = $this;
	}

	/**
	 * Get database path
	 *
	 * @return string
	 */
	private function getDatabasePath(): string {
		return $this->basePath() . DIRECTORY_SEPARATOR . 'database';
	}

	/**
	 * Get public path
	 *
	 * @return string
	 */
	private function getPublicPath(): string {
		return $this->basePath() . DIRECTORY_SEPARATOR . 'public';
	}

	/**
	 * Get routing path
	 *
	 * @return string
	 */
	private function getRoutePath(): string {
		return $this->basePath() . DIRECTORY_SEPARATOR . 'routes';
	}

	/**
	 * Get config path
	 *
	 * @return string
	 */
	private function getConfigPath(): string {
		return $this->basePath() . DIRECTORY_SEPARATOR . 'config';
	}

	/**
	 * Get cache path
	 *
	 * @return string
	 */
	private function getCachePath(): string {
		return $this->getStoragePath() . DIRECTORY_SEPARATOR . 'cache';;
	}

	/**
	 * Register instance of something
	 *
	 * @param string $key
	 * @param mixed $instance
	 */
	private function instance(string $key, mixed $instance): void {
		$this->instances[$key] = $instance;
	}

	private function hasInstance(string $key): bool {
		return isset($this->instances[$key]);
	}

	/**
	 * Returns the base path of the application, optionally joined with a provided path string.
	 * If no path is provided, this method returns the base path of the application. Otherwise, it joins the provided path string
	 * with the base path using the appropriate directory separator, and returns the resulting path string.
	 * @param string $path (optional) A path string to join with the base path.
	 * @return string The resulting path string.
	 */
	public function getBasePath(string $path = ''): string {
		return !$path ? $this->basePath : $this->basePath . DIRECTORY_SEPARATOR . $path;
	}


	/**
	 * Get base path of installation
	 *
	 * @param string $path
	 * @return string
	 */
	public function basePath(string $path = ''): string {
		return !$path ? $this->basePath : $this->basePath . (DIRECTORY_SEPARATOR . $path);
	}

	/**
	 * Get storage path
	 *
	 * @return string
	 */
	private function getStoragePath(): string {
		return $this->basePath() . DIRECTORY_SEPARATOR . 'storage';
	}

	/**
	 * Returns an instance of the Container class, ensuring that only a single instance is created.
	 * If no instance of the Container class currently exists, a new instance is created using the provided arguments.
	 * Otherwise, the existing instance is returned.
	 * @return Container An instance of the Container class.
	 */
	public static function getInstance(): Container {
		if (!self::$instance) {
			return new self(...func_get_args());
		}

		return self::$instance;
	}

	/**
	 * The make method in this class is used to either retrieve an already instantiated instance of the requested entity or to build a new instance by calling the resolve method.
	 * First, it checks if an instance of the requested entity already exists in the $instances array, and if so, it returns it.
	 * Otherwise, it calls the resolve method to create a new instance of the entity and returns it.
	 * This method allows the user to easily obtain an instance of any registered entity, whether it is a bound instance or a resolved instance.
	 * @param string $entity
	 * @return mixed
	 * @throws AppException|ReflectionException
	 */
	public function make(string $entity): mixed {
		return $this->instances[$entity] ?? $this->resolve($entity);
	}


	/**
	 * Register a concrete to abstract
	 * @param string $abstract
	 * @param mixed $concrete
	 * @return void
	 */
	public function singleton(string $abstract, mixed $concrete): void {
		$this->bind($abstract, $concrete, true);
	}

	/**
	 * Binding abstract to classes
	 * @param string $abstract
	 * @param mixed $concrete
	 * @param bool $shared
	 *
	 * @return void
	 */
	public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void {
		if (is_null($concrete)) {
			$concrete = $abstract;
		}
		if (!$concrete instanceof Closure) {
			$concrete = $this->getClosure($concrete);
		}

		$this->bindings[$abstract] = compact('concrete', 'shared');
	}

	/**
	 * The resolve() method takes a string $entity representing the class or interface to be resolved.
	 * It checks whether the entity can be resolved, and if so, it creates a new instance of the entity by calling the build() method.
	 * If the entity has been bound as a shared instance and has not yet been resolved, it adds the object to the resolved entities array.
	 * The method returns the resolved object.
	 * @param string $entity
	 * @return object|null
	 * @throws AppException|ReflectionException
	 */
	public function resolve(string $entity): null|object {
		if (!$this->canResolve($entity)) {
			throw new AppException("Cannot resolve entity `{$entity}`. It's has not bidding yet.");
		}

		$object = $this->build($entity);
		if ($this->bound($entity) && $this->takeBound($entity)['shared'] === true && !$this->isResolved($entity)) {
			$this->addResolve($entity, $object);
		}
		return $object;
	}

	/**
	 * The canResolve() method takes a string $entity representing the class or interface to be resolved, and returns a boolean indicating whether the entity can be resolved.
	 * The method checks if the entity has been bound to the container, if the class exists or if the entity has already been instantiated and registered as a shared instance.
	 * @param string $entity
	 * @return bool
	 */
	public function canResolve(string $entity): bool {
		return $this->bound($entity) || class_exists($entity) || $this->hasInstance($entity);
	}

	/**
	 * @throws AppException
	 */
	private function addResolve(string $abstract, mixed $concrete): void {
		if ($this->isResolved($abstract)) {
			throw new AppException("Duplicated abstract resolve `{$abstract}`");
		}
		$this->resolves[$abstract] = $concrete;
	}

	/**
	 * The bound() method takes a string $entity representing the class or interface to be resolved,
	 * and returns a boolean indicating whether the entity has been bound to the container.
	 * @param string $entity
	 * @return bool
	 */
	public function bound(string $entity): bool {
		return isset($this->bindings[$entity]);
	}

	/**
	 * The build() method takes a string $concrete representing the class to be instantiated.
	 * It checks if the concrete class has already been resolved, if the concrete class has been bound to the container, or if the class is instantiable.
	 * If the constructor for the class has dependencies, it recursively resolves them by calling the make() method.
	 * The method then returns a new instance of the concrete class with its resolved dependencies.
	 * @param mixed $concrete
	 * @return null|object
	 * @throws ReflectionException
	 * @throws AppException
	 */

	/**
	 * @throws ReflectionException
	 * @throws AppException
	 */
	public function build(mixed $concrete): null|object {

		if (is_string($concrete) && $this->isResolved($concrete)) {
			return $this->takeResolved($concrete);
		}

		if ($concrete instanceof Closure) {
			return call_user_func($concrete, $this);
		}

		if ($this->bound($concrete)) {
			return $this->build($this->takeBound($concrete)['concrete']);
		}

		$reflector = new \ReflectionClass($concrete);

		if (!$reflector->isInstantiable()) {
			throw new AppException("Class `{$concrete}` is not an instantiable");
		}

		if (is_null($constructor = $reflector->getConstructor())) {
			return new $concrete;
		}
		$dependencies = $constructor->getParameters();
		$instances = $this->resolveContractorHaveDependencies($dependencies);

		return $reflector->newInstanceArgs($instances);
	}

	/**
	 * Resolve all the dependencies from the ReflectionParameters.
	 * @param array $dependencies
	 * @return array
	 * @throws AppException|ReflectionException
	 */
	private function resolveContractorHaveDependencies(array $dependencies): array {
		$array = [];

		foreach ($dependencies as $dependency) {
			if ($this->isReflectionClass($dependency)) {
				$object = $dependency->getType()->getName();
				$array[$dependency->getName()] = $this->make($object);
			}
		}

		return $array;
	}

	private function isReflectionClass(\ReflectionParameter $reflectionParameter): bool {
		$type = $reflectionParameter->getType();
		return $type && !$type?->isBuiltin();
	}

	/**
	 * Resolve list of dependencies from options
	 *
	 * @param string $controller
	 * @param string $methodName
	 * @param array $params
	 *
	 * @return array
	 *
	 * @throws AppException
	 */
	public function resolveMethodDependencyWithParameters(string $controller, string $methodName, array $params): array {
		try {
			$ref = new \ReflectionMethod($controller, $methodName);
			$listParameters = $ref->getParameters();
			$array = [];
			foreach ($listParameters as $parameter) {
				switch (true) {
					case $this->isReflectionClass($parameter):
						$object = $this->buildStacks(
							$parameter->getType()->getName()
						);
						if ($object instanceof \Fast\Eloquent\Model) {
							$arg = array_shift($params);
							if (!$arg) {
								throw new AppException("Missing parameter `{$parameter->getName()}` 
								for initial model `{$parameter->getType()->getName()}`");
							}
							$object = $object->findOrFail($arg);
						}
						$array = [...$array, $object];
						break;
					case is_null($parameter->getType()->getName()):
						$param = array_shift($params);
						try {
							$default = $parameter->getDefaultValue();
						} catch (\ReflectionException $e) {
							$default = null;
						}

						if (!is_null($parameter->getType())) {
							switch ($parameter->getType()->getName()) {
								case 'int':
								case 'integer':
									$param = (int)$param ?: $default;
									break;
								case 'array':
									$param = (array)$param ?: $default;
									break;
								case 'object':
									$param = (object)$param ?: $default;
									break;
								case 'float':
									$param = (float)$param ?: $default;
									break;
								case 'string':
									$param = (string)$param ?: $default;
									break;
								case 'boolean':
								case 'bool':
									$param = (bool)$param ?: $default;
									break;
							}
						}

						$array = [...$array, $param];
						break;
					default:
						throw new AppException('Invalid type of parameter');

				}
			}
			return $array;
		} catch (ReflectionException $e) {
			throw new AppException($e->getMessage());
		}
	}

	private function isResolved(string $abstract): bool {
		return isset($this->resolves[$abstract]);
	}

	private function takeResolved(string $concrete) {
		return $this->resolves[$concrete];
	}

	private function takeBound(string $concrete) {
		return $this->bindings[$concrete];
	}

	/**
	 * @throws ReflectionException
	 * @throws AppException
	 */
	private function buildStacks(mixed $object): object {
		try {
			$object = $this->build($object);
			if ($object instanceof FormRequest) {
				$object->executeValidate();
			}
			return $object;
		} catch (\ArgumentCountError $e) {
			throw new AppException($e->getMessage());
		}
	}

	/**
	 * Get list of bindings
	 *
	 * @return array
	 */
	public function getBindings(): array {
		return $this->bindings;
	}

	/**
	 * Check is down for maintenance
	 *
	 * @return bool
	 */
	public function isDownForMaintenance(): bool {
		return false;
	}

	/**
	 * Should skip global middlewares
	 *
	 * @return bool
	 */
	public function skipMiddleware(): bool {
		return $this->skipMiddleware;
	}

	/**
	 * Get OS specific
	 *
	 * @return string
	 */
	public function getOS(): string {
		return match (true) {
			stristr(PHP_OS, 'DAR') => 'macosx',
			stristr(PHP_OS, 'WIN') => 'windows',
			stristr(PHP_OS, 'LINUX') => 'linux',
			default => 'unknown',
		};
	}

	/**
	 * Check is Windows system
	 *
	 * @return bool
	 */
	public function isWindows(): bool {
		return 'windows' === $this->getOs();
	}

	/**
	 * Check is MocOS system
	 *
	 * @return bool
	 */
	public function isMacos(): bool {
		return 'macosx' === $this->getOs();
	}

	/**
	 * Check is Linux system
	 *
	 * @return bool
	 */
	public function isLinux(): bool {
		return 'linux' === $this->getOs();
	}

	/**
	 * Check is Unknown system
	 *
	 * @return bool
	 */
	public function unknownOs(): bool {
		return 'unknown' === $this->getOs();
	}

	/**
	 * Get the Closure to be used when building a type.
	 *
	 * @param string $concrete
	 * @return Closure
	 */
	private function getClosure(mixed $concrete): Closure {
		return function () use ($concrete) {
			return $this->build($concrete);
		};
	}
}