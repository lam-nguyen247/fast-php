<?php
namespace Fast\Hashing;

use Fast\Contracts\Hashing\Hashes;

class BcryptHashes implements Hashes
{
	/**
	 * Hash the given value.
	 *
	 * @param  string  $value
	 * @param  array   $options
	 * @return string
	 *
	 */
	public function make(string $value, array $options = []): string {
		return password_hash($value, PASSWORD_BCRYPT, $options);
	}

	public function check(string $value, string $hashedValue, array $options = []): bool {
		if(strlen($hashedValue) === 0) {
			return false;
		}
		return password_verify($value, $hashedValue);
	}
}