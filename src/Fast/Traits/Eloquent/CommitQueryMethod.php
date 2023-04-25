<?php
namespace Fast\Traits\Eloquent;

use ReflectionException;
use Fast\Http\Exceptions\AppException;
use Fast\Database\QueryBuilder\QueryException;

trait CommitQueryMethod
{
	/**
	 * Execute the query as a "select" statement.
	 *
	 * @return mixed
	 * @throws AppException
	 * @throws QueryException
	 * @throws ReflectionException
	 */
	public function get(): mixed {
		$sql = $this->passe();
		return $this->request($sql);
	}

	/**
	 * View query builder to sql statement.
	 *
	 * @return void
	 */
	public function toSql(): void {
		echo $this->passe();
		exit(0);
	}

	/**
	 * Get full sql statement
	 *
	 * @return string
	 */
	public function getFullSql(): string {
		return $this->passe();
	}

	public function passe(): string{
		if (empty($this->table)) {
			return false;
		}
		$sql = $this->compile->compileSelect($this->distinct);
		$sql .= $this->compile->compileColumns($this->columns);
		$sql .= $this->compile->compileFrom($this->table);
		if (isset($this->joins)) {
			$sql .= $this->compile->compileJoins($this->joins);
		}
		if (isset($this->wheres)) {
			$sql .= $this->compile->compileWheres($this->wheres);
		}
		if (isset($this->wherein)) {
			$sql .= $this->compile->compileWhereIn($this->wherein);
		}
		if (isset($this->groups)) {
			$sql .= $this->compile->compileGroups($this->groups);
		}
		if (isset($this->havings)) {
			$sql .= $this->compile->compileHaving($this->havings);
		}
		if (isset($this->orders)) {
			$sql .= $this->compile->compileOrders($this->orders);
		}
		if (isset($this->limit)) {
			$sql .= $this->compile->compileLimit($this->limit);
		}
		if (isset($this->offset)) {
			$sql .= $this->compile->compileOffset($this->offset);
		}
		return $sql;
	}

	/**
	 * Create new record
	 *
	 * @param array $data
	 *
	 * @return mixed
	 * @throws AppException
	 * @throws QueryException
	 * @throws ReflectionException
	 */
	public function insert(array $data): mixed {
		$sql = $this->compile->compileInsert($this->table, $data);
		return $this->request($sql);
	}

	/**
	 * Create new record
	 *
	 * @param array $data
	 *
	 * @return mixed
	 *
	 * @throws AppException|ReflectionException
	 */
	public function create(array $data): mixed {
		if (!empty($this->calledFromModel)) {
			$object = new $this->calledFromModel;
			$fillable = $object->fillable();
			$hidden = $object->hidden();
			$columns = array_merge($fillable, $hidden);
			$sql = $this->compile->compileCreate($object, $columns, $data);
			return $this->request($sql);
		}
		throw new AppException("Method 'create' doesn't exists");
	}

	/**
	 * Find 1 record usually use column id
	 *
	 * @param string $value
	 * @param string $column
	 * @return mixed
	 * @throws AppException
	 * @throws QueryException
	 * @throws ReflectionException
	 */
	public function find(string $value, string $column = 'id'): mixed {
		$this->find = true;
		$this->limit = 1;
		$this->where($column, '=', $value);
		$sql = $this->compile->compileSelect($this->distinct);
		$sql .= $this->compile->compileColumns($this->columns);
		$sql .= $this->compile->compileFrom($this->table);
		$sql .= $this->compile->compileWheres($this->wheres);
		return $this->request($sql);
	}

	/**
	 * Find 1 record usually use column id
	 *
	 * @param string $value
	 * @param string $column
	 * @return mixed
	 * @throws AppException
	 * @throws QueryException
	 * @throws ReflectionException
	 */
	public function findOrFail(string $value, string $column = 'id'): mixed {
		$this->find = true;
		$this->limit = 1;
		$this->isThrow = true;
		$this->where($this->calledFromModel ? $this->getCalledModelInstance()->primaryKey() : $column, '=', $value);
		$sql = $this->compile->compileSelect($this->distinct);
		$sql .= $this->compile->compileColumns($this->columns);
		$sql .= $this->compile->compileFrom($this->table);
		$sql .= $this->compile->compileWheres($this->wheres);
		return $this->request($sql);
	}

	/**
	 * First 1 record usually use column id
	 *
	 * @return mixed
	 * @throws AppException
	 * @throws QueryException
	 * @throws ReflectionException
	 */
	public function first(): mixed {
		$this->first = true;
		$this->limit = 1;
		$sql = $this->passe();
		return $this->request($sql);
	}

	/**
	 * First 1 record usually use column id
	 *
	 * @return mixed
	 * @throws AppException
	 * @throws QueryException
	 * @throws ReflectionException
	 */
	public function firstOrFail(): mixed {
		$this->first = true;
		$this->isThrow = true;
		$this->limit = 1;
		$sql = $this->passe();
		return $this->request($sql);
	}

	/**
	 * Quick login with array params
	 *
	 * @param array $data
	 * @return mixed
	 * @throws AppException
	 * @throws QueryException
	 * @throws ReflectionException
	 */
	public function login(array $data): mixed {
		$this->find = true;
		$sql = $this->compile->compileLogin($this->table, $data);
		return $this->request($sql);
	}

	/**
	 * Destroy a record from condition
	 *
	 * @return mixed
	 * @throws AppException
	 * @throws QueryException
	 * @throws ReflectionException
	 */
	public function delete(): mixed {
		$sql = $this->compile->compileDelete($this->table);

		if (!is_null($this->existsModelInstance)) {
			$model = $this->existsModelInstance;
			$primaryKey = $model->primaryKey();
			$valueKey = $model->$primaryKey;
			$this->where($primaryKey, $valueKey);
		}
		$sql .= $this->compile->compileWheres($this->wheres);

		return $this->request($sql);
	}

	/**
	 * Update records from condition
	 *
	 * @param array $data
	 * @return mixed
	 * @throws AppException
	 * @throws QueryException
	 * @throws ReflectionException
	 */
	public function update(array $data)
	{
		$sql = $this->compile->compileUpdate($this->table, $data);

		if (!is_null($this->existsModelInstance)) {
			$model = $this->existsModelInstance;
			$primaryKey = $model->primaryKey();
			$valueKey = $model->$primaryKey;
			$this->where($primaryKey, $valueKey);
			$sql .= $this->compile->compileWheres($this->wheres);
		}

		return $this->request($sql);
	}

	/**
	 * Begin transaction
	 *
	 * @return bool
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function beginTransaction(): bool
	{
		return app()->make('connection')->getConnection()->{__FUNCTION__}();
	}

	/**
	 * Commit transaction
	 *
	 * @return bool
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function commit(): bool
	{
		return app()->make('connection')->getConnection()->{__FUNCTION__}();
	}

	/**
	 * Rollback transaction
	 *
	 * @return bool
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function rollBack(): bool
	{
		return app()->make('connection')->getConnection()->{__FUNCTION__}();
	}
}