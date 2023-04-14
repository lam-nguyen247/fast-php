<?php
namespace Fast\Auth;

use DB;
use Hash;
use Session;
use Firebase\JWT\JWT;
use Fast\Eloquent\Model;
use Fast\Http\Exceptions\AppException;
use Fast\Contracts\Auth\Authentication;

class Authenticatable implements Authentication
{

	private string $guard = '';

	private string $provider = '';

	private string $model = '';

	private ?Model $object = null;

	public function attempt(array $options = []): bool {
		$model = new $this->model;
		$columnPassword = $model->password();
		$table = $model->table();
		$paramPassword = $options[$columnPassword];
		unset($options[$columnPassword]);

		$object = DB::table($table)->where($options)->first();
		if(!$object || !Hash::check($paramPassword, $object->password)){
			return false;
		}

		return $this->setUserAuth(
			$this->model::where($options)->firstOrFail()
		);
	}

	public function user(): ?Model {
		// TODO: Implement user() method.
	}

	public function logout(): void {
		// TODO: Implement logout() method.
	}

	public function check(): bool {
		// TODO: Implement check() method.
	}

	public function guard(string $guard = ''): Authentication {
		// TODO: Implement guard() method.
	}
}
