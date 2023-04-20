<?php
namespace Fast\Queues;

use ReflectionProperty;

abstract class Queue
{
	/**
	 * The times of tries
	 *
	 * @var int
	 */
	protected int $tries = 3;

	abstract function handle(): void;

	public function getTries(): int
	{
		return $this->tries;
	}

	/**
	 * Prepare the instance for serialization.
	 *
	 * @return array
	 */
	public function getSerializeData(): array
	{
		$properties = (new \ReflectionClass($this))->getProperties();
		$data = [];

		foreach ($properties as $property){
			$data[$property->getName()] = $this->getPropertyValue($property);
		}

		return $data;
	}

	/**
	 * Get the property value for the given property.
	 *
	 * @param  ReflectionProperty  $property
	 * @return mixed
	 */
	public function getPropertyValue(ReflectionProperty $property): mixed
	{
		$property->setAccessible(true);

		return $property->getValue($this);
	}
}