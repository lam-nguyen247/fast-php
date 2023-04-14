<?php
namespace Fast\Hashing;

use Fast\Contracts\Hashing\Hasher;

class BcryptHasher implements Hasher
{
	/**
	 * Hash the given value.
	 *
	 * @param  string  $value
	 * @param  array   $options
	 * @return string
	 *
	 * @throws HashException
	 */
	public function make(string $value, array $options = []): string {
		$hash = password_hash($value, PASSWORD_BCRYPT, $options);
		if($hash === false) {
			throw new HashException('Bcrypt hashing not supported.');
		}

		return $hash;
	}

	public function check(string $value, string $hashedValue, array $options = []): bool {
		if(strlen($hashedValue) === 0) {
			return false;
		}

		return password_verify($value, $hashedValue);
	}
}