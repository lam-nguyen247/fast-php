<?php
namespace Fast;

use Fast\Http\Exceptions\AppException;

class Container
{
	/**
	 * Version of the application
	 *
	 * @const  VERSION
	 */
	const VERSION = "1.0.0";

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
	public function __construct(string $basePath)
	{
		$this->basePath = $basePath;

		$this->instance('path.route', $this->getPathFor('routes'));
		$this->instance('path.cache', $this->getPathFor('cache'));
		$this->instance('path.config', $this->getPathFor('config'));
		$this->instance('path.public', $this->getPathFor('public'));
		$this->instance('path.storage', $this->getPathFor('storage'));
		$this->instance('path.database', $this->getPathFor('database'));

		self::$instance = $this;
	}

	/**
	 * Register instance of something
	 *
	 * @param string $key
	 * @param mixed $instance
	 */
	private function instance(string $key, $instance): void
	{
		$this->instances[$key] = $instance;
	}

	private function hasInstance(string $key): bool
	{
		return isset($this->instances[$key]);
	}

	/**
	* Returns the base path of the application, optionally joined with a provided path string.
	* If no path is provided, this method returns the base path of the application. Otherwise, it joins the provided path string
	* with the base path using the appropriate directory separator, and returns the resulting path string.
	* @param string $path (optional) A path string to join with the base path.
	* @return string The resulting path string.
	 */
	public function getBasePath(string $path=''): string
	{
		return !$path ? $this->basePath : $this->basePath . DIRECTORY_SEPARATOR . $path ;
	}



	/**
	Returns the absolute path to a file or directory within the application directory structure.
	This method takes a string parameter representing the name of the file or directory, and returns the absolute path
	to that file or directory within the application directory structure. The method uses the getBasePath() method to
	determine the base path of the application, and then joins the provided name with the base path using the appropriate
	directory separator to form the absolute path.
	* @param string $name The name of the file or directory.
	* @return string The absolute path to the file or directory within the application directory structure.
	 */
	private function getPathFor(string $name): string
	{
		return $this->getBasePath() . DIRECTORY_SEPARATOR . $name;
	}

	/**
	* Returns an instance of the Container class, ensuring that only a single instance is created.
	* If no instance of the Container class currently exists, a new instance is created using the provided arguments.
	* Otherwise, the existing instance is returned.
	* @return Container An instance of the Container class.
	 */
	public static function getInstance(): Container
	{
		if(!self::$instance){
			return new self(...func_get_args());
		}

		return self::$instance;
	}

	/**
	 * The make method in this class is used to either retrieve an already instantiated instance of the requested entity or to build a new instance by calling the resolve method.
	 * First, it checks if an instance of the requested entity already exists in the $instances array, and if so, it returns it. Otherwise, it calls the resolve method to create a new instance of the entity and returns it.
	 * This method allows the user to easily obtain an instance of any registered entity, whether it is a bound instance or a resolved instance.
	 * @param string $entity
	 * @return null|object
	 * @throws AppException
	 */
	public function make(string $entity): null|object
	{
		return $this->instances[$entity] ?? $this->resolve($entity);
	}

	/**
	 * The resolve() method takes a string $entity representing the class or interface to be resolved.
	 * It checks whether the entity can be resolved, and if so, it creates a new instance of the entity by calling the build() method.
	 * If the entity has been bound as a shared instance and has not yet been resolved, it adds the object to the resolved entities array.
	 * The method returns the resolved object.
	 * @param string $entity
	 * @return object|null
	 * @throws AppException
	 */
	public function resolve(string $entity): null|object
	{
		if(!$this->canResolve($entity)){
			throw new AppException("Cannot resolve entity `{$entity}`. It's has not bidding yet.");
		}

		$object = $this->build($entity);

		if($this->bound($entity) && $this->takeBound($entity)['shared'] === true && !$this->isResolved($entity)){
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
	public function canResolve(string $entity): bool
	{
		return $this->bound($entity) || class_exists($entity) || $this->hasInstance($entity);
	}

	private function addResolve(string $abstract, mixed $concrete): void
	{
		if ($this->isResolved($abstract)) {
			throw new \Fast\Http\Exceptions\AppException("Duplicated abstract resolve `{$abstract}`");
		}

		$this->resolves[$abstract] = $concrete;
	}

	/**
	 * The bound() method takes a string $entity representing the class or interface to be resolved,
	 * and returns a boolean indicating whether the entity has been bound to the container.
	 * @param string $entity
	 * @return bool
	 */
	public function bound(string $entity): bool
	{
		return isset($this->bindings[$entity]);
	}

	/**
	 * The build() method takes a string $concrete representing the class to be instantiated.
	 * It checks if the concrete class has already been resolved, if the concrete class has been bound to the container, or if the class is instantiable.
	 * If the constructor for the class has dependencies, it recursively resolves them by calling the make() method.
	 * The method then returns a new instance of the concrete class with its resolved dependencies.
	 * @param mixed $concrete
	 * @return null|object
	 * @throws \ReflectionException
	 * @throws AppException
	 */
	public function build(mixed $concrete): null|object
	{
		if(is_string($concrete) && $this->isResolved($concrete)){
			return $this->takeResolved($concrete);
		}

		if($this->bound($concrete)){
			return $this->build($this->takeBound($concrete)['concrete']);
		}

		$reflector = new \ReflectionClass($concrete);
		if(!$reflector->isInstantiable()){
			throw new AppException("Class `{$concrete}` is not an instantiable");
		}

		if(is_null($constructor = $reflector->getConstructor())){
			return new $concrete;
		}

		$dependencies = $constructor->getParameters();
		$intances = $this->resolveContractorHaveDependencies($dependencies);

		return $reflector->newInstanceArgs($intances);

	}

	/**
	 * Resolve all the dependencies from the ReflectionParameters.
	 * @param array $dependencies
	 * @return array
	 * @throws AppException
	 */
	private function resolveContractorHaveDependencies(array $dependencies): array
	{
		$array = [];
		foreach ($dependencies as $dependency) {
			if ($dependency->getClass() instanceof \ReflectionClass) {
				$object = $dependency->getClass()->getName();
				$array[$dependency->getName()] = $this->make($object);
			}
		}

		return $array;
	}

	private function isResolved(string $abstract): bool
	{
		return isset($this->resolves[$abstract]);
	}

	private function takeResolved(string $concrete)
	{
		return $this->resolves[$concrete];
	}

	private function takeBound(string $concrete)
	{
		return $this->bindings[$concrete];
	}
}