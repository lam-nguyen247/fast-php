<?php

namespace Fast\Traits\Eloquent;

use Str;

trait GetAttribute {
	/**
	 * Get table
	 *
	 * @return string
	 */
	public function table(): string {
		if (isset($this->table)) {
			return $this->table;
		}

		return strtolower(
			Str::pluralize(
				snake_case(
					class_name_only(static::class)
				)
			)
		);
	}

	/**
	 * Get password
	 *
	 * @return string
	 */
	public function password(): string {
		return $this->password;
	}

	/**
	 * Get primary key
	 *
	 * @return string
	 */
	public function primaryKey(): string {
		return $this->primaryKey;
	}

	/**
	 * Get created at
	 *
	 * @return string
	 */
	public function createdAt(): string {
		return self::CREATED_AT;
	}

	/**
	 * Get updated at
	 *
	 * @return string
	 */
	public function updatedAt(): string {
		return self::UPDATED_AT;
	}

	/**
	 * Get list appends
	 *
	 * @return array
	 */
	public function appends(): array {
		return $this->appends;
	}

	/**
	 * Get list fillable
	 *
	 * @return array
	 */
	public function fillable(): array {
		return $this->fillable;
	}

	/**
	 * Get list casts
	 *
	 * @return array
	 */
	public function casts(): array {
		return $this->casts;
	}

	/**
	 * Get list hidden
	 *
	 * @return array
	 */
	public function hidden(): array {
		return $this->hidden;
	}
}
