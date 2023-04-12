<?php
namespace Fast\Services;

use http\Exception\InvalidArgumentException;

abstract class Enum
{
	/**
	 * The value of the Enum instance.
	 *
	 * @var mixed
	 */
	protected mixed $value;

	/**
	 * Creates a new Enum instance with the given value.
	 *
	 * @param mixed $value The value of the Enum instance.
	 * @throws InvalidArgumentException if the provided value is not valid.
	 */
	public function __construct(mixed $value)
	{
		if(!$this->isValidValue($value)){
			throw new InvalidArgumentException("Invalid value provided");
		}

		$this->value = $value;
	}

	/**
	 * Returns the string representation of the Enum instance.
	 *
	 * @return string The string representation of the Enum instance.
	 */
	public function __toString(): string
	{
		return (string) $this->value;
	}

	/**
	 * Returns the value of the Enum instance.
	 *
	 * @return mixed The value of the Enum instance.
	 */

	public function getValue(): mixed
	{
		return $this->value;
	}

	/**
	 * Returns an array of all the constants defined in the Enum class.
	 *
	 * @return array An array of all the constants defined in the Enum class.
	 */

	public static function toArray(): array
	{
		$ref = new \ReflectionClass(statis::class);
		return $ref->getConstants();
	}

	/**
	 * Returns a boolean indicating whether the given value is a valid value for the Enum class.
	 *
	 * @param mixed $value The value to check.
	 * @return bool Whether the value is valid for the Enum class.
	 */
	public static function isValidValue(mixed $value): bool
	{
		$ref = new \ReflectionClass(static:: class);
		$values = array_values($ref->getConstants());
		return in_array($value, $values, true);
	}
}