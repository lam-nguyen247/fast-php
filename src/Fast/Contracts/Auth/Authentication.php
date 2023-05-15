<?php
namespace Fast\Contracts\Auth;

use Fast\Eloquent\Model;

interface Authentication {
	public function attempt(array $options = []): bool;

	public function user(): ?Model;

	public function logout(): bool;

	public function check(): bool;

	public function guard(string $guard = ''): Authentication;
}
