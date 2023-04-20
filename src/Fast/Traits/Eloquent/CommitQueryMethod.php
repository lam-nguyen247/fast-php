<?php
namespace Fast\Traits\Eloquent;

use JetBrains\PhpStorm\NoReturn;

trait CommitQueryMethod
{
	/**
	 * Execute the query as a "select" statement.
	 *
	 * @return mixed
	 */
	public function get() {
		$sql = $this->passe();
		return $this->request($sql);
	}

	/**
	 * View query builder to sql statement.
	 *
	 * @return void
	 */
	#[NoReturn] public function toSql(): void {
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
}