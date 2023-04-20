<?php
namespace Fast\Traits\Validator;

use Fast\Services\File;
use ReflectionException;
use Fast\Http\Exceptions\AppException;
use DB;

trait Verify
{
	/**
	 * @throws ReflectionException
	 * @throws AppException
	 */
	public function min(mixed $value , array $rules, float $min): void
	{
		switch (true) {
			case in_array('number', $rules):
				if($min > (int) $value) {
					$this->pushErrorMessage($this->current, $this->buildErrorMessage([$this->current, 'number'], __FUNCTION__, [
						'min' => $min
					]));
				}
				break;
			case in_array('file', $rules) || in_array('video', $rules) || in_array('audio', $rules)
				|| in_array('image', $rules):
				$sizeMb = $value->size / 1000 / 1000;
				if($min > $sizeMb) {
					$this->pushErrorMessage($this->current, $this->buildErrorMessage([$this->current, 'file'], __FUNCTION__, [
						'min' => $min
					]));
				}
				break;
			case 'string':
			default:
				if(strlen((string) $value) < $min){
					$this->pushErrorMessage($this->current, $this->buildErrorMessage([$this->current, 'string'], __FUNCTION__,[
						'min' => $min
					]));
				}
		}
	}

	/**
	 * Validate max type
	 *
	 * @param mixed $value
	 * @param array $rules
	 * @param float $max
	 *
	 * @return void
	 * @throws ReflectionException
	 * @throws AppException
	 */
	public function max($value, array $rules, float $max): void
	{
		switch (true) {
			case in_array('number', $rules):
				if ($max < (int) $value) {
					$this->pushErrorMessage($this->current, $this->buildErrorMessage([$this->current, 'number'], __FUNCTION__, [
						'max' => $max
					]));
				}
				break;
			case in_array('file', $rules) || in_array('video', $rules) || in_array('audio', $rules) || in_array('image', $rules):
				$sizeMb = $value->size / 1000 / 1000;
				if ($max < $sizeMb) {
					$this->pushErrorMessage($this->current, $this->buildErrorMessage([$this->current, 'file'], __FUNCTION__, [
						'max' => $max
					]));
				}
				break;
			case 'string':
			default:
				if (strlen((string) $value) > $max) {
					$this->pushErrorMessage($this->current, $this->buildErrorMessage([$this->current, 'string'], __FUNCTION__, [
						'max' => $max
					]));
				}
		}
	}

	/**
	 * Validate number type
	 *
	 * @param mixed $value
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function number(mixed $value): void
	{
		if (!is_numeric($value)) {
			$this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__));
		}
	}

	/**
	 * Validate string type
	 *
	 * @param mixed $value
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function string(mixed $value): void
	{
		if (!is_string($value)) {
			$this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__));
		}
	}

	/**
	 * Validate required type
	 *
	 * @param mixed $value
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function required(mixed $value): void
	{
		if (empty($value)) {
			$this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__));
		}
	}

	/**
	 * Validate file type
	 *
	 * @param mixed $value
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function file(mixed $value): void
	{
		if (!$value instanceof File) {
			$this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__));
		}
	}

	/**
	 * Validate image type
	 *
	 * @param mixed $value
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function image(mixed $value): void
	{
		if (!$value instanceof File || !str_contains($value->type, 'image/')) {
			$this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__));
		}
	}

	/**
	 * Validate audio type
	 *
	 * @param mixed $value
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function audio(mixed $value): void
	{
		if (!$value instanceof File || str_contains($value->type, 'audio/')) {
			$this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__));
		}
	}

	/**
	 * Validate video type
	 *
	 * @param mixed $value
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function video(mixed $value): void
	{
		if (!$value instanceof File || str_contains($value->type, 'video/')) {
			$this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__));
		}
	}

	/**
	 * Validate email type
	 *
	 * @param mixed $value
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function email(mixed $value): void
	{
		if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
			$this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__));
		}
	}

	/**
	 * Validate unique type
	 *
	 * @param mixed $value
	 * @param string $ruleValue
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function unique(mixed $value, string $ruleValue): void
	{
		[$table, $columnValue] = explode(',', $ruleValue);
		if (str_contains($columnValue, ';')) {
			[$column, $keyValue] = explode(';', $columnValue);
		} else {
			$column = $columnValue;
		}

		$table = DB::table($table)->where($column, $value)->first();
		if ($table && isset($keyValue) && $table->$column != $keyValue || $table && !isset($keyValue)) {
			$this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__));
		}
	}

	/**
	 * Validate unique type
	 *
	 * @param mixed $value
	 * @param string $ruleValue
	 *
	 * @return void
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function exists(mixed $value, string $ruleValue): void
	{
		[$table, $columnValue] = explode(',', $ruleValue);
		if (str_contains($columnValue, ';')) {
			[$column, $keyValue] = explode(';', $columnValue);
		} else {
			$column = $columnValue;
		}
		$table = DB::table($table)->where($column, $value)->first();
		if (is_null($table)) {
			$this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__));
		}
	}

	/**
	 * Handle custom rule
	 *
	 * @param string $rule
	 *
	 * @return void
	 */
	public function handleCustomRule(string $rule): void
	{
		$handle = $this->getCustom($rule);
		if (!$handle($this->passable)) {
			$this->pushErrorMessage($this->current, $this->customMessages[$rule]);
		}
	}
}