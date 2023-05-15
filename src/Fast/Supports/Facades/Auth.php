<?php

namespace Fast\Supports\Facades;

use Fast\Supports\Facade;

/**
 * @method static attempt(array $array)
 * @method static user()
 * @method static logout()
 */
class Auth extends Facade {
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor(): string {
		return 'auth';
	}
}
