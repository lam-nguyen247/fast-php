<?php

namespace Fast\Eloquent;

class Collection extends \ArrayObject {

	protected array $items = [];

	public function __construct(object|array $array = [], int $flags = 0, string $iteratorClass = 'ArrayIterator') {
		$collections = [];
		foreach ($array as $value) {
			if (($value instanceof Model)) {
				$value = $value->getData();
			}
			$collections[] = $value;
		}
		parent::__construct($collections, $flags, $iteratorClass);
		$this->items = $collections;
	}

	public function map(callable $callback): Collection {
		$mapped_data = array_map($callback, $this->getArrayCopy());
		return new Collection($mapped_data);
	}

	public function filter(callable $callback = null): Collection {
		if ($callback) {
			$filtered_data = array_filter($this->getArrayCopy(), $callback);
		} else {
			$filtered_data = array_filter($this->getArrayCopy());
		}
		return new Collection($filtered_data);
	}

	public function pluck($column): Collection {
		$plucked_data = array_column($this->getArrayCopy(), $column);
		return new Collection($plucked_data);
	}

	public function sum($key = null): float|int {
		if ($key) {
			$summed_data = array_sum($this->pluck($key)->toArray());
		} else {
			$summed_data = array_sum($this->getArrayCopy());
		}
		return $summed_data;
	}

	/**
	 * Get all the items in the collection.
	 *
	 * @return array<TKey, TValue>
	 */
	public function all(): array {
		return $this->items;
	}

	public function toArray(): array {
		return $this->getArrayCopy();
	}

}
