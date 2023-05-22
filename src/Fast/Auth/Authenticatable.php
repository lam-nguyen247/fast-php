<?php
namespace Fast\Auth;

use Log;
use DB;
use Hash;
use Session;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Fast\Eloquent\Model;
use ReflectionException;
use Firebase\JWT\ExpiredException;
use Fast\Http\Exceptions\AppException;
use Fast\Contracts\Auth\Authentication;
use Firebase\JWT\SignatureInvalidException;

class Authenticatable implements Authentication {

	private string $guard = '';

	private string $provider = '';

	private string $model = '';

	private ?Model $object = null;

	/**
	 * @throws AuthenticationException
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function attempt(array $options = []): bool {
		$model = new $this->model;
		$columnPassword = $model->password();
		$table = $model->table();
		$paramPassword = $options[$columnPassword];
		unset($options[$columnPassword]);
		$object = DB::table($table)->select('password')->where($options)->first();
		if (!$object || !Hash::check($paramPassword, $object->password)) {
			return false;
		}
		return $this->setUserAuth(
			$this->model::where($options)->firstOrFail()
		);
	}

	/**
	 */
	public function user(): ?Model {
		return $this->getObject();

	}

	/**
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function logout(): bool {
		$guardDriver = $this->getConfigDriverFromGuard(
			$this->getCurrentGuard()
		);

		switch ($guardDriver) {
			case 'session':
				Session::unset('user');
				return true;
			case 'jwt':
				$this->user()->token = '';
				$this->user()->save();
				return true;
		}
		return false;
	}

	/**
	 * Make true format for jwt key
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function trueFormatKey(string $key): string {
		return base64_decode(strtr($key, '-_', '+/'));
	}

	/**
	 * @throws AppException
	 * @throws AuthenticationException
	 * @throws ReflectionException
	 */
	public function check(): bool {
		$guardDriver = $this->getConfigDriverFromGuard($this->getCurrentGuard());
		switch ($guardDriver) {
			case 'session':
				return Session::get('user');
			case 'jwt':
				$key = config('jwt.secret');
				$hash = config('jwt.hash');

				if (empty($key)) {
					throw new AuthenticationException('Please install the JWT authentication');
				}

				if (empty($hash)) {
					throw new AuthenticationException('Please set hash type in config/jwt.php');
				}
				$header = app()->getRequest()->headers->get('Authorization');
				if (empty($header)) {
					return false;
				}
				$bearerToken = str_replace('Bearer ', '', $header);
				try {
					$jwt = app()->make(JWT::class);
					$decode = $jwt->decode($bearerToken, new Key($this->trueFormatKey($key), $hash));
					$model = new $this->model;
					$user = $this->model::where('token', '<>', '')->where('id', '=', $decode->{$model->primaryKey()})->first();
					if($user != null){
						$this->setObject($user);
					}
					return (bool)$user;
				} catch (ExpiredException|SignatureInvalidException $e) {
					Log::error($e->getMessage());
					throw new AppException('Unauthorized', 401);
				}
			default:
				throw new AppException('Unauthorized', 401);
		}
	}

	/**
	 * Set user to application
	 *
	 * @param Model $user
	 *
	 * @return bool
	 *
	 * @throws AuthenticationException
	 * @throws AppException|ReflectionException
	 */
	private function setUserAuth(Model $user): bool {
		$this->setObject($user);

		$guardDriver = $this->getConfigDriverFromGuard(
			$this->getCurrentGuard()
		);

		switch ($guardDriver) {
			case 'session':
				Session::set('user', $this->getObject()->getData());
				break;
			case 'jwt':
				break;
			default:
				throw new AuthenticationException('Unknown authentication');
		}

		return true;
	}

	/**
	 * Set guard for authentication
	 *
	 * @param string $guard
	 *
	 * @return $this
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function guard(string $guard = ''): Authenticatable {
		if (empty($guard)) {
			$guard = $this->getDefaultGuard();
		}

		$this->setGuard($guard);

		$guard = $this->getCurrentGuard();

		$this->setProvider(
			$this->getConfigProviderFromGuard($guard)
		);

		$provider = $this->getProvider();

		$this->setModel(
			$this->getConfigModelFromProvider($provider)
		);

		return $this;
	}

	/**
	 * Set object
	 *
	 * @param Model $object
	 *
	 * @return void
	 */
	protected function setObject(Model $object): void {
		$this->object = $object;
	}

	/**
	 * Get current object bound
	 *
	 * @return null|Model
	 */
	protected function getObject(): ?Model {
		return $this->object;
	}

	/**
	 * Get configuration model from provider
	 *
	 * @param string $provider
	 *
	 * @return string
	 * @throws AppException
	 * @throws ReflectionException
	 */
	protected function getConfigModelFromProvider(string $provider): string {
		return config("auth.providers.{$provider}.model");
	}

	/**
	 * Get configuration provider from guard
	 *
	 * @param string $guard
	 *
	 * @return string
	 * @throws AppException|ReflectionException
	 */
	protected function getConfigProviderFromGuard(string $guard): string {
		return config("auth.guards.{$guard}.provider");
	}

	/**
	 * Get configuration provider from guard
	 *
	 * @param string $guard
	 *
	 * @return string
	 * @throws AppException
	 * @throws ReflectionException
	 */
	protected function getConfigDriverFromGuard(string $guard): string {
		return config("auth.guards.{$guard}.driver");
	}

	/**
	 * Set model name
	 *
	 * @param string $model
	 *
	 * @return void
	 */
	protected function setModel(string $model): void {
		$this->model = $model;
	}

	/**
	 * Get current provider
	 *
	 * @return string
	 */
	protected function getProvider(): string {
		return $this->provider;
	}

	/**
	 * Set provider
	 *
	 * @param string $provider
	 *
	 * @return void
	 */
	public function setProvider(string $provider): void {
		$this->provider = $provider;
	}

	/**
	 * Set guard
	 *
	 * @param string $guard
	 *
	 * @return void
	 */
	public function setGuard(string $guard): void {
		$this->guard = $guard;
	}

	/**
	 * Get default guard config
	 *
	 * @return string
	 * @throws AppException
	 * @throws ReflectionException
	 */
	private function getDefaultGuard(): string {
		return config('auth.defaults.guard');
	}

	/**
	 * Get current guard config
	 *
	 * @return string
	 */
	public function getCurrentGuard(): string {
		return $this->guard;
	}
}
