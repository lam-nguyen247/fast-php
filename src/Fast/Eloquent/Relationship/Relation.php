<?php
namespace Fast\Eloquent\Relationship;

use Closure;
use Fast\Eloquent\Model;

abstract class Relation
{
	/**
	 * Model that is belongs to this
	 *
	 * @var string
	 */
	protected string $model;

	/**
	 * Instance of model
	 *
	 * @var Model
	 */
	protected Model $instance;

	/**
	 * Where condition
	 *
	 * @var array
	 */
	protected array $wheres = [];

	/**
	 * List of where conditions
	 *
	 * @var array
	 */
	protected array $conditions = [
		'=', '>=', '<=', '<', '>', '<>'
	];

	/**
	 * Method execute
	 *
	 * @var string
	 */
	const METHOD_EXECUTION = 'getModelObject';

	/**
	 * Get data of relationship
	 *
	 * @param string $value
	 * @param Closure|null $callback
	 *
	 * @return mixed
	 */
	abstract public function getModelObject(string $value, ? Closure $callback = null): mixed;

	public function where(string $column, string $condition = '=', string $value = ''): Relation {
		if(!in_array($condition, $this->getAcceptCondition())) {
			$where = [$column, '=', $condition];
		}else {
			$where = [$column, $condition, $value];
		}
		$this->wheres = [...$this->getWhereCondition(), $where];

		return $this;
	}

	public function setModelInstance(Model $instance): void{
		$this->instance = $instance;
	}

	public function getModelInstance(): Model{
		return $this->instance;
	}

	public function setModel(string $model): void{
		$this->model = $model;
	}

	public function getModel(): string{
		return $this->model;
	}

	/**
	 * Get list of accept conditions
	 *
	 * @return array
	 */
	protected function getAcceptCondition(): array
	{
		return $this->conditions;
	}

	/**
	 * Get where condition
	 *
	 * @return array
	 */
	public function getWhereCondition(): array
	{
		return $this->wheres;
	}
}