<?php

namespace Fast\Supports\Facades;

use Fast\Supports\Facade;

class Creator extends Facade {
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor(): string {
		return \Fast\Contracts\Console\Kernel::class;
	}
}
