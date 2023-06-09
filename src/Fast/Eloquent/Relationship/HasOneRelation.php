<?php
namespace Fast\Eloquent\Relationship;

use Closure;
use ReflectionException;
use Fast\Http\Exceptions\AppException;
use Fast\Database\QueryBuilder\QueryBuilder;
use Fast\Database\QueryBuilder\QueryException;

class HasOneRelation extends Relation {
	protected string $localKey = '';

	protected string $localValue = '';

	protected string $remoteKey = '';

	public function __construct(string $model) {
		$this->setModel($model);
	}

	public function setLocalKey(string $localKey): void {
		if (empty($localKey)) {
			$model = $this->getModel();
			$localKey = (new $model)->primaryKey();
		}
		$this->localKey = $localKey;
	}

	public function getLocalKey(): string {
		return $this->localKey;
	}

	public function setLocalValue(string $localValue): void {
		$this->localValue = $localValue;
	}

	public function getLocalValue(): string {
		return $this->localValue;
	}

	public function setRemoteKey(string $remoteKey): void {
		$this->remoteKey = $remoteKey;
	}

	public function getRemoteKey(): string {
		return $this->remoteKey;
	}

	/**
	 * @param string $value
	 * @param Closure|null $callback
	 * @return mixed
	 * @throws AppException
	 * @throws ReflectionException
	 * @throws QueryException
	 */
	public function getModelObject(string $value, ?Closure $callback = null): mixed {
		$builder = $this->buildWhereCondition($value);

		if (!is_null($callback)) {
			$callback($builder);
		}
		return $builder->first();
	}

	/**
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function __call(string $method, array $args) {
		return $this->buildWhereCondition($this->getLocalValue())->$method(...$args);
	}

	/**
	 * @param string $value
	 * @return QueryBuilder
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function buildWhereCondition(string $value): QueryBuilder {
		$builder = app()->make($this->getModel())->where($this->getRemoteKey(), $value);
		foreach ($this->getWhereCondition() as $where) {
			$builder->where(array_shift($where), array_shift($where), array_shift($where));
		}

		return $builder;
	}
}