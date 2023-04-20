<?php
namespace Fast\Database\QueryBuilder;

use Fast\Eloquent\Model;
use Fast\Database\QueryBuilder\QueryException;

class Compile
{
	public function compileSelect(bool $distinct): string {
		return $distinct ? 'SELECT DISTINCT ' : 'SELECT ';
	}

	public function compileColumns(array $columns = []): string {
		return !empty($columns) ? implode(', ', $columns): ' * ';
	}

	public function compileFrom(string $table): string {
		return " FROM {$table} ";
	}

	public function compileJoins(array $joins): string {
		$sql = "";
		foreach ($joins as $join){
			$j = !empty($join[4]) ? strtoupper($join[4]) : 'INNER';
			$sql .= " {$j} JOIN {$join[0]} ON {$join[1]} {$join[2]} {$join[3]} ";
		}

		return $sql;
	}

	/**
	 * Compiles an array of WHERE conditions into a SQL WHERE clause string.
	 *
	 * @param array $wheres Array of WHERE conditions.
	 * @return string Compiled WHERE clause string.
	 */
	public function compileWheres(array $wheres): string
	{
		if (empty($wheres)) {
			return "";
		}

		$sql = " WHERE ";
		$conditions = [];

		foreach ($wheres as $where) {
			$condition = "";

			if ($where[0] == 'start_where') {
				$condition .= '(';
			} elseif ($where[0] == 'start_or') {
				$condition .= '(';
				$condition .= " OR ";
			} elseif ($where[0] == 'end_where' || $where[0] == 'end_or') {
				$condition .= ')';
			} else {
				$condition .= " {$where[0]} {$where[1]} ?";
			}

			if (!empty($condition)) {
				$conditions[] = $condition;
			}
		}

		$sql .= implode(' AND ', $conditions);

		return $sql;
	}

	/**
	 * Compile group by
	 *
	 * @param array $groups
	 *
	 * @return string
	 */
	public function compileGroups(array $groups): string
	{
		return !empty($groups) ? " GROUP BY " . implode(', ', $groups) : "";
	}

	/**
	 * Compiles an array of HAVING conditions into a SQL HAVING clause string.
	 *
	 * @param array $havings Array of HAVING conditions.
	 * @return string Compiled HAVING clause string.
	 */
	public function compileHaving(array $havings): string
	{
		if (empty($havings)) {
			return "";
		}

		$sql = " HAVING ";
		$conditions = [];

		foreach ($havings as $having) {
			$condition = "{$having[0]} {$having[1]} ?";

			if (!empty($having[3])) {
				$condition .= " {$having[3]} ";
			} else {
				$condition .= " AND ";
			}

			$conditions[] = $condition;
		}

		$sql .= implode('', $conditions);

		return $sql;
	}

	/**
	 * Compile order
	 *
	 * @param array $orders
	 *
	 * @return string
	 */
	public function compileOrders(array $orders): string
	{
		if (empty($orders)) {
			return "";
		}

		$sql = " ORDER BY ";
		$count = count($orders);
		for ($i = 0; $i < $count; $i++) {
			$order = $orders[$i];
			$sql .= "$order[0] $order[1]";
			if ($i < $count - 1) {
				$sql .= ", ";
			}
		}

		return $sql;
	}

	/**
	 * Compile limit
	 *
	 * @param int $limit
	 *
	 * @return string
	 */
	public function compileLimit(int $limit): string
	{
		return $limit ? " LIMIT {$limit} " : "";
	}

	/**
	 * Compile offset
	 *
	 * @param int $offset
	 *
	 * @return string
	 */
	public function compileOffset(int $offset): string
	{
		return $offset ? " OFFSET {$offset}" : "";
	}

	/**
	 * Compile where in
	 *
	 * @param array $wherein
	 *
	 * @return string
	 */
	public function compileWhereIn(array $wherein): string {
		if (empty($wherein)) {
			return "";
		}

		$array = array_map(function($value) {
			return "'" . $value . "'";
		}, explode(", ", $wherein[1]));

		return " WHERE {$wherein[0]} IN (" . implode(", ", $array) . ")";
	}

	/**
	 * Compile insert
	 *
	 * @param string $table
	 * @param array $data
	 *
	 * @return string
	 */
	public function compileInsert(string $table, array $data): string
	{
		$columns = implode(', ', array_keys($data));
		$placeholders = implode(', ', array_fill(0, count($data), '?'));

		return "INSERT INTO $table ($columns) VALUES ($placeholders)";
	}

	/**
	 * @throws QueryException|\Fast\Database\QueryBuilder\QueryException
	 */
	public function compileCreate(Model $model, array $fillable, array $data): string
	{
		try {
			$columns = [];
			$values = [];
			foreach ($fillable as $column) {
				if (isset($data[$column])) {
					$ucFirst = ucfirst($column);
					$settingMethod = "set{$ucFirst}Attribute";
					if (method_exists($model, $settingMethod)) {
						$values[] = "'" . call_user_func([$model, $settingMethod], $data[$column]) . "'";
					} else {
						$values[] = "'$data[$column]'";
					}
					$columns[] = $column;
				}
			}
			$columns = implode(', ', $columns);
			$values = implode(', ', $values);
			$table = $model->table();
			return "INSERT INTO $table($columns) VALUES ($values)";
		} catch (\Exception $e) {
			throw new QueryException($e->getMessage());
		}
	}

	/**
	 * Compile delete
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	public function compileDelete(string $table): string
	{
		return "DELETE FROM {$table}";
	}

	/**
	 * Compile update
	 *
	 * @param string $table
	 * @param array $arg
	 *
	 * @return string
	 */
	public function compileUpdate(string $table, array $arg): string
	{
		$sql = "UPDATE {$table} SET ";
		foreach ($arg as $key => $dt) {
			$sql .= "$key = '$dt', ";
		}
		$length = strlen($sql);
		return substr($sql, 0, $length - 2);
	}
}