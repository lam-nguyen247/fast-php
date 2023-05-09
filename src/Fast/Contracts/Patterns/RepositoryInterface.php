<?php

namespace Fast\Contracts\Patterns;

use Closure;

interface RepositoryInterface {
	/**
	 * @param array $columns
	 * @return mixed
	 */
	public function all(array $columns = ['*']): mixed;

	/**
	 * @param $column
	 * @param null $key
	 * @return mixed
	 */
	public function lists($column, $key = null): mixed;

	/**
	 * @param null $limit
	 * @param array $columns
	 * @return mixed
	 */
	public function paginate($limit = null, array $columns = ['*']): mixed;

	/**
	 * Find
	 *
	 * @param $id
	 * @param array $column
	 * @return mixed
	 */
	public function find($id, array $column = ['*']): mixed;

	/**
	 * Find or fail
	 *
	 * @param $id
	 * @return mixed
	 */
	public function findOrFail($id): mixed;

	/**
	 * Where
	 *
	 * @param $condition
	 * @param null $operator
	 * @param null $value
	 * @return mixed
	 */
	public function where($condition, $operator = null, $value = null): mixed;

	/**
	 * Or where
	 *
	 * @param $column
	 * @param null $operator
	 * @param null $value
	 * @return mixed
	 */
	public function orWhere($column, $operator = null, $value = null): mixed;

	/**
	 * First or create
	 *
	 * @param array $input
	 * @return mixed
	 */
	public function firstOrCreate(array $input = []): mixed;

	/**
	 * Insert
	 *
	 * @param $input
	 * @return mixed
	 */
	public function insert($input): mixed;

	/**
	 * Insert
	 *
	 * @param $input
	 * @return mixed
	 */
	public function create(array $input): mixed;

	/**
	 * @param $id
	 * @param $input
	 * @return mixed
	 */
	public function update($id, $input): mixed;

	/**
	 * @param $column
	 * @param $value
	 * @param $input
	 * @return mixed
	 */
	public function multiUpdate($column, $value, $input): mixed;

	/**
	 * @param $ids
	 * @return mixed
	 */
	public function delete($ids): mixed;

	/**
	 * Load relations
	 *
	 * @param $relations
	 * @return mixed
	 */
	public function with($relations): mixed;

	/**
	 * With
	 *
	 * @param $column
	 * @param string $direction
	 * @return mixed
	 */
	public function orderBy($column, string $direction = 'asc'): mixed;

	/**
	 * With count
	 *
	 * @param $relation
	 * @return mixed
	 */
	public function withCount($relation): mixed;

	/**
	 * select
	 *
	 * @param array $columns
	 * @return mixed
	 */
	public function select(array $columns = ['*']): mixed;

	/**
	 * Where has
	 * @param $relation
	 * @param $closure
	 * @return mixed
	 */
	public function whereHas($relation, $closure): mixed;

	/**
	 * Where in
	 *
	 * @param $column
	 * @param array $values
	 *
	 * @return $this
	 */
	public function whereIn($column, array $values): static;

	/**
	 * Where not in
	 *
	 * @param $column
	 * @param array $values
	 *
	 * @return $this
	 */
	public function whereNotIn($column, array $values): static;

	/**
	 * Check if entity has relation
	 *
	 * @param string $relation
	 *
	 * @return $this
	 */
	public function has(string $relation): static;

	/**
	 * Join with other take
	 * @param string $table
	 * @param string|null $columnTableA
	 * @param string|null $condition
	 * @param string|null $columnTableB
	 *
	 * @return $this
	 */
	public function join(string $table, string $columnTableA = null, string $condition = null, string $columnTableB = null): static;

	/**
	 * When function check condition to execute query
	 * @param string $condition
	 * @param Closure $callback
	 * @param Closure|null $default
	 *
	 * @return $this
	 */
	public function when(string $condition, Closure $callback, Closure $default = null): static;
}
