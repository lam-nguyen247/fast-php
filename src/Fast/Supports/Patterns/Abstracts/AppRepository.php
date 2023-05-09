<?php

namespace Fast\Supports\Patterns\Abstracts;

use Closure;
use Fast\Eloquent\Model;
use ReflectionException;
use Fast\Http\Exceptions\AppException;
use Fast\Database\QueryBuilder\QueryBuilder;
use Fast\Contracts\Patterns\RepositoryInterface;

abstract class AppRepository implements RepositoryInterface {
	/**
	 * @var Model
	 */
	protected Model $model;

	/**
	 * AppRepository constructor.
	 * @throws AppException
	 * @throws ReflectionException
	 */

	public function __construct() {
		$this->makeModel();
	}

	/**
	 * get model
	 * @return string
	 */
	abstract public function model(): string;

	/**
	 * Checking connection to database
	 *
	 * @return bool
	 * @throws AppException
	 */
	private function isConnected(): bool {
		return app('connection')->isConnected();
	}

	/**
	 * Set model
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function makeModel(): void {
		if (!$this->isConnected()) {
			app('connection')->setDriver('backup');
		}
		$model = $this->model();
		if (!app()->make($model)) {
			app()->singleton($model, function () use ($model) {
				return new $model;
			});
		}
		$this->model = app()->make($model);
	}

	/**
	 * If missing any method for repository
	 * it's will be call with default
	 * @param string $method
	 * @param array $args
	 *
	 * @return QueryBuilder|mixed
	 */
	public function __call(string $method, array $args): mixed {
		return $this->model->$method(...$args);
	}

	/**
	 * Get All
	 *
	 * @param array $columns
	 * @return mixed
	 */
	public function all(array $columns = ['*']): mixed {
		return $this->model->all($columns);
	}

	/**
	 * Lists
	 *
	 * @param $column
	 * @param null $key
	 * @return mixed
	 */
	public function lists($column, $key = null): mixed {
		return $this->model->pluck($column, $key);
	}

	/**
	 * Paginate
	 *
	 * @param null $limit
	 * @param array $columns
	 * @return mixed
	 * @throws AppException
	 */
	public function paginate($limit = null, array $columns = ['*']): mixed {
		$limit = is_null($limit) ? config('settings.paginate') : $limit;
		return $this->model->paginate($limit, $columns);
	}

	/**
	 * Find
	 *
	 * @param $id
	 * @param array $column
	 * @return mixed
	 */
	public function find($id, array $column = ['*']): mixed {
		return $this->model->find($id, $column);
	}

	public function findOrFail($id, $column = ['*']): mixed {
		return $this->model->findOrFail($id, $column);
	}

	/**
	 * Find
	 *
	 * @return mixed
	 */
	public function first(): mixed {
		return $this->model->first();
	}

	/**
	 * Where
	 *
	 * @param $condition
	 * @param null $operator
	 * @param null $value
	 * @return mixed
	 */
	public function where($condition, $operator = null, $value = null): mixed {
		return $this->model->where($condition, $operator, $value);
	}

	/**
	 * Or where
	 *
	 * @param $column
	 * @param null $operator
	 * @param null $value
	 * @return mixed
	 */
	public function orWhere($column, $operator = null, $value = null): mixed {
		return $this->model->orWhere($column, $operator, $value);
	}

	/**
	 * Create
	 *
	 * @param array $input
	 * @return mixed
	 */
	public function firstOrCreate(array $input = []): mixed {
		return $this->model->firstOrCreate($input);
	}

	/**
	 * Insert
	 *
	 * @param $input
	 * @return mixed
	 */
	public function insert($input): mixed {
		return $this->model->insert($input);
	}

	/**
	 * Insert
	 *
	 * @param $input
	 * @return mixed
	 */
	public function create(array $input): mixed {
		return $this->model->create($input);
	}

	/**
	 * @param $id
	 * @param $input
	 * @return mixed
	 */
	public function update($id, $input): mixed {
		$result = $this->model->find($id);
		if ($result) {
			$result->update($input);
			return $result;
		}

		return false;
	}

	/**
	 * Update or create
	 *
	 * @param $input
	 * @param $id
	 * @return mixed
	 */

	public function updateOrCreate($id, $input): mixed {
		$result = $this->model->find($id);
		if ($result) {
			$result->update($input);
			return $result;
		}
		return $this->model->updateOrCreate($input);
	}

	/**
	 * Multi Update
	 *
	 * @param $column
	 * @param $value
	 * @param $input
	 * @return mixed
	 */
	public function multiUpdate($column, $value, $input): mixed {
		$value = is_array($value) ? $value : [$value];
		return $this->model->whereIn($column, $value)->update($input);
	}

	/**
	 * Delete
	 *
	 * @param $ids
	 * @return mixed
	 */
	public function delete($ids): mixed {
		if (empty($ids)) {
			return true;
		}
		$ids = is_array($ids) ? $ids : [$ids];

		return $this->model->destroy($ids);
	}

	/**
	 * Soft Delete
	 *
	 * @param $name
	 * @param $ids
	 * @param $input
	 * @return mixed
	 */
	public function softDelete($name, $ids, $input): mixed {
		if (empty($ids)) {
			return true;
		}
		$ids = is_array($ids) ? $ids : [$ids];

		return $this->multiUpdate($name, $ids, $input);
	}

	/**
	 * @param $relations
	 * @return mixed
	 */
	public function with($relations): mixed {
		return $this->model->with($relations);
	}

	/**
	 * Order by
	 *
	 * @param $column
	 * @param string $direction
	 * @return mixed
	 */
	public function orderBy($column, string $direction = 'asc'): mixed {
		return $this->model->orderBy($column, $direction);
	}

	/**
	 * With count
	 *
	 * @param $relation
	 * @return mixed
	 */
	public function withCount($relation): mixed {
		return $this->model->withCount($relation);
	}

	/**
	 * Select
	 *
	 * @param array $columns
	 * @return mixed
	 */
	public function select(array $columns = ['*']): mixed {
		return $this->model->select($columns);
	}

	/**
	 * Load relation with closure
	 *
	 * @param $relation
	 * @param $closure
	 * @return mixed
	 */
	public function whereHas($relation, $closure): mixed {
		return $this->model->whereHas($relation, $closure);
	}

	/**
	 * where in
	 *
	 * @param $column
	 * @param array $values
	 *
	 * @return $this
	 */
	public function whereIn($column, array $values): static {
		$values = is_array($values) ? $values : [$values];
		return $this->model->whereIn($column, $values);
	}

	/**
	 * where not in
	 *
	 * @param $column
	 * @param mixed $values
	 *
	 * @return $this
	 */
	public function whereNotIn($column, mixed $values): static {
		$values = is_array($values) ? $values : [$values];
		return $this->model->whereNotIn($column, $values);
	}

	/**
	 * @param string $relation
	 * @return $this
	 */
	public function has(string $relation): static {
		return $this->model->with($relation);
	}

	/**
	 * Join with other take
	 * @param string $table
	 * @param string|null $columnTableA
	 * @param string|null $condition
	 * @param string|null $columnTableB
	 *
	 * @return $this
	 */
	public function join(string $table, string $columnTableA = null, string $condition = null, string $columnTableB = null): static {
		return $this->model->join($table, $columnTableA, $condition, $columnTableB);
	}

	/**
	 * When function check condition to execute query
	 * @param string $condition
	 * @param Closure $callback
	 * @param Closure|null $default
	 *
	 * @return $this
	 */
	public function when(string $condition, Closure $callback, Closure $default = null): static {
		return $this->model->when($condition, $callback, $default);
	}

}
