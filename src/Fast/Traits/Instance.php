<?php
namespace Fast\Traits;

use Fast\Application;
use Fast\Eloquent\Model;

/**The Instance trait provides a way to implement the Singleton pattern by allowing only one instance of a class to be created.
 * This trait defines a private static property called '$instance', which is used to store the singleton instance of the class.
 * The trait also provides a constructor that sets the '$instance' property to the current instance of the class.
 * Additionally, the trait provides a static factory method called 'getInstance()' that returns the singleton instance of the class.
 * If no instance currently exists, this method creates a new instance and sets it as the singleton instance.*/
trait Instance {
	/**
	 * the singleton instance of the class
	 **/
	private static self $instance;

	public function __construct() {
		self::$instance = $this;
	}

	/**
	 * Returns the singleton instance of the class.
	 * If no instance currently exists, this method creates a new instance of the class and sets it as the singleton instance.
	 * @return Model|Application|Instance The singleton instance of the class.
	 */
	public static function getInstance(): self {
		if (!self::$instance) {
			static::$instance = new static;
		}

		return static::$instance;
	}
}