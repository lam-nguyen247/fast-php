<?php

namespace Fast;

use ReflectionException;
use Fast\Http\Exceptions\AppException;

class AliasLoader {
	/**
	 * Initial alias loader
	 *
	 * @method aliasLoader()
	 */
	public function __construct() {
		spl_autoload_register([$this, 'aliasLoader']);
	}

	/**
	 * Listen loading classes
	 * @param string $class
	 * @return bool
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function aliasLoader(string $class): bool {
		$aliases = config('app.aliases');

		if (isset($aliases[$class])) {
			return class_alias($aliases[$class], $class);
		}

		return true;
	}
}