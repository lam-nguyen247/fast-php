<?php

namespace Fast\Traits\Eloquent;

use Fast\Eloquent\Model;
use Fast\Eloquent\EloquentException;
use Fast\Http\Exceptions\AppException;
use Fast\Database\QueryBuilder\QueryException;

trait HandleCompileWithBuilder {
	/**
	 * Instance of exists model
	 *
	 * @var Model | null
	 */
	protected ?Model $existsModelInstance = null;

	/**
	 * List of with relations
	 */
	public array $with = [];

	/**
	 * Create new query builder from model
	 *
	 * @param string $table
	 * @param array $modelMeta
	 * @param string $method
	 * @param array|null $args
	 * @param Model|null $instance
	 *
	 * @return mixed
	 *
	 * @throws QueryException
	 */
	public function staticEloquentBuilder(string $table, array $modelMeta, string $method, ?array $args = null, ?Model $instance = null): mixed {
		try {
			$object = isset($modelMeta['calledClass']) ? new self($table, $modelMeta['calledClass']) : new self($table);
			switch ($method) {
				case 'find':
				case 'findOrFail':
					try {
						[$value] = $args;
						return $object->$method($value, $modelMeta['primaryKey']);
					} catch (\TypeError $e) {
						throw new \Exception($e->getMessage());
					}
				case 'with':
					$object->with = $args && is_array($args[0]) ? $args[0] : $args;
					return $object;
				case 'update':
				case 'delete':
					$object->existsModelInstance = $instance;
					break;
				default:
					try {
						if (method_exists($object, $method)) {
							return $object->$method(...$args);
						}
						$buildScope = $this->buildScopeMethod($method);
						$objectModel = new $modelMeta['calledClass'];
						if (method_exists($objectModel, $buildScope)) {
							return $objectModel->$buildScope($object, ...$args);
						}
					} catch (\TypeError $e) {
						throw new \Exception($e->getMessage());

					}
			}
			throw new AppException("Method `{$method}` does not exist");
		} catch (\Exception $e) {
			throw new QueryException($e->getMessage());
		}
	}

	/**
	 * Handle call
	 *
	 * @param string $method
	 * @param array $args
	 *
	 * @return Model
	 *
	 * @throws EloquentException
	 */
	public function __call(string $method, array $args): Model {
		try {
			$instance = new $this->calledFromModel;

			if (method_exists($instance, $method)) {
				return $instance->$method(...$args);
			}
			$buildScope = $this->buildScopeMethod($method);
			if (method_exists($instance, $buildScope)) {
				array_unshift($args, $this);
				return $instance->$buildScope(...$args);
			}
			throw new EloquentException("Method `$method` does not exist");
		} catch (\TypeError $e) {
			throw new EloquentException($e->getMessage());
		}
	}

	/**
	 * Make scope method
	 *
	 * @param string $method
	 *
	 * @return string
	 */
	private function buildScopeMethod(string $method): string {
		return 'scope' . ucfirst($method);
	}

	/**
	 * Set with option
	 *
	 * @param array|string $with
	 *
	 * @return self
	 */
	public function with(array|string $with): self {
		$this->with = is_array($with) ? $with : func_get_args();
		return $this;
	}
}
