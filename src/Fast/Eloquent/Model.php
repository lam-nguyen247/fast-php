<?php

namespace Fast\Eloquent;

use DB;
use Fast\Traits\Instance;
use Fast\Eloquent\EloquentException;
use Fast\Traits\Eloquent\GetAttribute;
use Fast\Traits\Eloquent\RelationTraits;
use Log;


/**
 * @method create(array $all)
 * @method update(array $all)
 * @method delete(mixed $id)
 */
abstract class Model {
	use GetAttribute, Instance, RelationTraits;

	/**
	 * List of appends
	 *
	 * @var array
	 */
	protected array $appends = [];

	/**
	 * List of custom attributes
	 *
	 * @var array
	 */
	protected array $customAttributes = [];

	/**
	 * List of attributes
	 *
	 * @var array
	 */
	protected array $attributes = [];

	/**
	 * List of casts
	 *
	 * @var array
	 */
	protected array $casts = [];

	/**
	 * Lit of fillable
	 *
	 * @var array
	 */
	protected array $fillable = [];

	/**
	 * List of hidden
	 *
	 * @var array
	 */
	protected array $hidden = [];

	/**
	 * Table instance
	 *
	 * @var string
	 */
	protected string $table;

	/**
	 * Primary key
	 *
	 * @var string
	 */
	protected string $primaryKey = 'id';

	/**
	 * Created at column name
	 *
	 * @var string
	 */
	const CREATED_AT = 'created_at';

	/**
	 * Updated at column name
	 *
	 * @var string
	 */
	const UPDATED_AT = 'updated_at';

	/**
	 * Initial constructor
	 */
	public function __construct() { }

	public function __set(string $name, mixed $value) {
		$this->setAttributes($name, $value);
	}

	public function __get(string $name) {
		return $this->getAttributes($name);
	}

	/**
	 * @throws EloquentException
	 */
	public function getData(array $collect = [], array $hidden = []): array {
		$data = [];
		$this->callServiceGetAttributes();
		$this->callServiceAppends();
		$this->callServiceCasts();

		if (!empty($collect)) {
			foreach ($collect as $item) {
				$data[$item] = $this->getAttributes($item);
			}
		} else {
			$data = $this->attributes;
		}

		return array_except($data, array_merge($hidden, $this->hidden));
	}

	/**
	 * Get the column attributes for the given model.
	 *
	 * @param
	 * @return mixed|null
	 */
	protected function getAttributes($name): mixed {
		return $this->attributes[$name] ?? null;
	}

	protected function setAttributes(string $name, mixed $value): void {
		$this->attributes[$name] = $value;
	}

	/**
	 * Handle call static
	 *
	 * @param string $method
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	public static function __callStatic(string $method, array $args) {
		$static = new static;
		$table = $static->table();
		$modelMeta = [
			'primaryKey' => $static->primaryKey(),
			'fillable' => $static->fillable(),
			'hidden' => $static->hidden(),
			'calledClass' => get_called_class(),
		];
		return DB::staticEloquentBuilder($table, $modelMeta, $method, $args);
	}

	/**
	 * Handle call
	 *
	 * @param string $method
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function __call(string $method, array $args) {
		$static = new static;
		$table = $static->table();
		$modelMeta = [
			'primaryKey' => $static->primaryKey(),
			'fillable' => $static->fillable(),
			'hidden' => $static->hidden(),
			'calledClass' => get_called_class(),
		];
		return DB::staticEloquentBuilder($table, $modelMeta, $method, $args, $this);
	}

	public function save() {
		$static = new static;
		$table = $static->table();
		$modelMeta = [
			'primaryKey' => $static->primaryKey(),
			'fillable' => $static->fillable(),
			'calledClass' => get_called_class(),
		];
		return DB::staticEloquentBuilder($table, $modelMeta, 'update', $this->getData(), $this);
	}

	/**
	 * Handle service appends
	 *
	 * @return void
	 */
	private function callServiceAppends(): void {
		foreach ($this->appends as $key => $value) {
			$values = explode('_', $value);
			$func = '';
			foreach ($values as $app) {
				$func .= ucfirst($app);
			}
			try {
				$this->$value = call_user_func([$this, "get{$func}Attribute"]);
			} catch (\Exception $e) {
				Log::error($e->getMessage());
			}
		}
	}

	/**
	 * Execute service casts
	 *
	 * @return void
	 *
	 * @throws EloquentException
	 */
	private function callServiceCasts(): void {
		foreach ($this->casts as $key => $value) {
			if (!in_array($key, $this->fillable)) {
				throw new EloquentException("The attribute $key not exists in fillable.");
			}
			switch ($value) {
				case 'int':
					$this->{$key} = isset($this->{$key}) ? (int)$this->{$key} : null;
					break;
				case 'array':
					$this->{$key} = isset($this->{$key}) ? (array)$this->{$key} : null;
					break;
				case 'object':
					$this->{$key} = isset($this->{$key}) ? (object)$this->{$key} : null;
					break;
				case 'double':
				case 'float':
					$this->{$key} = isset($this->{$key}) ? (float)$this->{$key} : null;
					break;
				case 'string':
					$this->{$key} = isset($this->{$key}) ? (string)$this->{$key} : null;
					break;
				case 'boolean':
					$this->{$key} = isset($this->{$key}) ? (bool)$this->{$key} : null;
					break;
			}
		}
	}

	/**
	 * Execute service get attribute
	 *
	 * @return void
	 */
	private function callServiceGetAttributes(): void {
		foreach (get_class_methods($this) as $key => $value) {
			if (str_contains($value, 'get') && str_contains($value, 'Attribute')) {
				$this->handleServiceGetAttribute($value);
			}
		}
	}

	/**
	 * Handle service get attribute
	 *
	 * @param string $value
	 *
	 * @return void
	 */
	private function handleServiceGetAttribute(string $value): void {
		$pazeGet = str_replace('get', '', $value);
		$pazeAttribute = str_replace('Attribute', '', $pazeGet);
		foreach ($this->fillable as $fillable) {
			$values = explode('_', $fillable);
			$func = '';
			foreach ($values as $app) {
				$func .= ucfirst($app);
			}
			if ($func == $pazeAttribute) {
				$this->$fillable = call_user_func([$this, "get{$func}Attribute"]);
			}
		}
	}

	/**
	 * Execute service hidden
	 *
	 * @return void
	 */
	public function callServiceHidden(): void {
		foreach ($this->hidden as $hidden) {
			unset($this->$hidden);
		}
	}
}
