<?php
namespace Fast\Traits\Eloquent;

use PDO;
use PDOStatement;
use ReflectionException;
use Fast\Eloquent\EloquentException;
use Fast\Http\Exceptions\AppException;
use Fast\Eloquent\ModelBindingObject;
use Fast\Database\QueryBuilder\QueryException;
trait ExecuteQuery
{
	/**
	 * @throws QueryException
	 * @throws AppException|ReflectionException
	 */
	public function request(string $sql)
	{
		try{
			$connection = app()->make('connection')->getConnection();
			switch (true) {
				case $this->isInsertQuery($sql):
					$object = $connection->query($sql);
					break;
				case $this->isSelectQuery($sql):
				case $this->isUpdateQuery($sql):
				case $this->isDeleteQuery($sql):
					$object = $connection->prepare($sql);
					$this->bindingParams($object);
					$object->execute();
					break;
			}
			$this->rowCount = $object->rowCount();
			return $this->buildResponse($sql, $object, $connection);
		}catch (\PDOException $e) {
			throw new QueryException($e->getMessage());
		}
	}

	/**
	 * Check is insert query
	 *
	 * @param string $query
	 *
	 * @return boolean
	 */
	public function isInsertQuery(string $query): bool
	{
		$parse = explode(' ', $query);
		$queryType = array_shift($parse);

		return 'INSERT' === strtoupper(trim($queryType));
	}

	/**
	 * Check is update query
	 *
	 * @param string $query
	 *
	 * @return boolean
	 */
	public function isUpdateQuery(string $query): bool
	{
		$parse = explode(' ', $query);
		$queryType = array_shift($parse);

		return 'UPDATE' === strtoupper(trim($queryType));
	}

	/**
	 * Check is select query
	 *
	 * @param string $query
	 *
	 * @return boolean
	 */
	public function isSelectQuery(string $query): bool
	{
		$parse = explode(' ', $query);
		$queryType = array_shift($parse);

		return 'SELECT' === strtoupper(trim($queryType));
	}

	/**
	 * Check is delete query
	 *
	 * @param string $query
	 *
	 * @return boolean
	 */
	public function isDeleteQuery(string $query): bool
	{
		$parse = explode(' ', $query);
		$queryType = array_shift($parse);

		return 'DELETE' === strtoupper(trim($queryType));
	}

	/**
	 * Building response
	 * @param string $sql
	 * @param PDOStatement $object
	 * @param PDO $connection
	 *
	 * @return mixed
	 * @throws EloquentException
	 * @throws AppException
	 */
	private function buildResponse(string $sql, PDOStatement $object, PDO $connection): mixed {
		$type = explode(" ", $sql);
		return match (array_shift($type)) {
			'SELECT' => $this->inCaseSelect($object),
			'INSERT' => $this->inCaseInsert($connection),
			'UPDATE', 'DELETE' => true,
			default => $object,
		};
	}

	/**
	 * Binding parameters to sql statements
	 *
	 * @param PDOStatement $object
	 *
	 * @return void
	 */
	private function bindingParams(PDOStatement $object): void
	{
		if (!is_null($this->parameters)) {
			foreach ($this->parameters as $key => &$param) {
				$object->bindParam($key + 1, $param);
			}
		}
	}

	/**
	 * Get one row has model instance
	 *
	 * @param PDO $connection
	 *
	 * @return mixed
	 */
	private function getOneItemHasModel(PDO $connection): mixed {
		$primaryKey = $this->getCalledModelInstance()->primaryKey();
		return $this->find($connection->lastInsertId(), $primaryKey);
	}

	private function getCalledModelInstance()
	{
		return new $this->calledFromModel;
	}

	/**
	 * Exec sql get column id in connection
	 *
	 * @param PDO $connection
	 *
	 * @return mixed
	 * @throws AppException
	 */
	private function sqlExecGetColumnIdInConnection(PDO $connection): mixed {
		$lastInsertId = $connection->lastInsertId();
		$getConfigFromConnection = app()->make('connection');
		$connection = $getConfigFromConnection->getConnection();
		$databaseName = $getConfigFromConnection->getConfig()[3];
		$newObject = $connection->prepare($this->createSqlStatementGetColumnName($databaseName));
		$newObject->execute();
		return $this->find($lastInsertId, $newObject->fetch()->COLUMN_NAME);
	}

	/**
	 * Create sql statement get column name
	 *
	 * @param string $databaseName
	 *
	 * @return string
	 */
	private function createSqlStatementGetColumnName(string $databaseName): string
	{
		return "
            SELECT
                COLUMN_NAME
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = '{$databaseName}' AND
                TABLE_NAME = '{$this->table}' AND EXTRA = 'auto_increment'
        ";
	}

	/**
	 * Handle in case insert SQL
	 *
	 * @param PDO $connection
	 *
	 * @return mixed
	 * @throws AppException
	 */
	private function inCaseInsert(PDO $connection): mixed {
		if (!empty($this->calledFromModel)) {
			return $this->getOneItemHasModel($connection);
		}
		return $this->sqlExecGetColumnIdInConnection($connection);
	}

	/**
	 * Handle in case select SQL
	 *
	 * @param PDOStatement $object
	 *
	 * @return array|mixed|object|null
	 * @throws EloquentException
	 */
	private function inCaseSelect(PDOStatement $object): mixed {
		if ($this->find === true || $this->first === true) {
			if (!empty($this->calledFromModel)) {
				return $this->execBindingModelObject($object);
			}
			return $object->fetch();
		}
		if (!empty($this->calledFromModel)) {
			return $this->execBindingModelObject($object);
		}
		return $this->fetchOneItemWithoutModel($object);
	}

	/**
	 * Execute binding model object
	 *
	 * @param PDOStatement $pdoStatementObject
	 *
	 * @return object|null
	 * @throws EloquentException
	 */
	private function execBindingModelObject(PDOStatement $pdoStatementObject): ?object
	{
		$resources = $pdoStatementObject->fetchAll(PDO::FETCH_CLASS, $this->calledFromModel);

		$binding = new ModelBindingObject($resources);

		return $binding->setTakeOne($this->find || $this->first)
			->setTakeList(!$this->find && !$this->first)
			->setIsThrow($this->isThrow)
			->setArgs([
				'with' => $this->with
			])
			->verifyEmptyResources()
			->handle();
	}

	/**
	 * Fetch one item without model
	 *
	 * @param PDOStatement $object
	 *
	 * @return array
	 */
	private function fetchOneItemWithoutModel(PDOStatement $object): array
	{
		return $object->fetchAll(PDO::FETCH_OBJ);
	}
}