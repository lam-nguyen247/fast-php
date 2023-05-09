<?php

namespace Fast\Database\DatabaseBuilder;

use ReflectionException;
use Fast\Http\Exceptions\AppException;
use Fast\Traits\DatabaseBuilder\MigrateBuilder;
use Fast\Database\DatabaseBuilder\DatabaseBuilderException;

class Schema {
	use MigrateBuilder;

	/**
	 * Handle call static
	 *
	 * @param string $method
	 * @param array $arguments
	 *
	 * @return void
	 *
	 * @throws DatabaseBuilderException
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public static function __callStatic(string $method, array $arguments): void {
		switch ($method) {
			case 'create':
				[$table, $columns] = $arguments;
				(new self)->createMigrate($table, $columns);
				break;
			case 'createIfNotExists':
				[$table, $columns] = $arguments;
				(new self)->createIfNotExistsMigrate($table, $columns);
				break;
			case 'drop':
				[$table] = $arguments;
				(new self)->dropMigrate($table);
				break;
			case 'dropIfExists':
				[$table] = $arguments;
				(new self)->dropIfExistsMigrate($table);
				break;
			case 'truncate':
				[$table] = $arguments;
				(new self)->truncateMigrate($table);
				break;
			default:
				throw new DatabaseBuilderException("Method '$method' is not supported.");
		}
	}
}
