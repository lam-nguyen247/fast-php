<?php
namespace Fast\Auth;

use DB;
use Hash;
use Session;
use Firebase\JWT\JWT;
use Fast\Eloquent\Model;
use ReflectionException;
use Firebase\JWT\ExpiredException;
use Fast\Http\Exceptions\AppException;
use Fast\Contracts\Auth\Authentication;
use Firebase\JWT\SignatureInvalidException;

class Authenticatable implements Authentication
{

	private string $guard = '';

	private string $provider = '';

	private string $model = '';

	private ?Model $object = null;

	/**
	 * @throws AuthenticationException
	 * @throws AppException
	 */
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

	/**
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function user(): ?Model {
		if(!is_null($this->getObject())) {
			return $this->getObject();
		}

		$guardDriver = $this->getConfigDriverFromGuard($this->getCurrentGuard());

		switch ($guardDriver) {
			case 'session':
				return Session::get('user');
			case 'jwt':
				$key = config('jwt.secret');
				$hash = config('jwt.hash');

				if(empty($key)) {
					throw new AuthenticationException('Please install the JWT authentication');
				}

				if(empty($hash)) {
					throw new AuthenticationException('Please set hash type in config/jwt.php');
				}
				$header = getallheaders();
				if(!isset($header['Authorization'])) {
					return null;
				}

				$bearerToken = str_replace('Bearer', '', $header['Authorization']);
				try {
					$jwt = app()->make(JWT::class);
					$decode = $jwt->decode($bearerToken, $this->trueFormatKey($key), [$hash]);
					$primaryKey = app()->make($this->model)->primaryKey();
					return $this->model::findOrFail($decode->object->{$primaryKey});
				}catch (ExpiredException|SignatureInvalidException $e){
					throw new AuthenticationException($e->getMessage());
				}
			default:
				throw new AuthenticationException('Unknown authentication');
		}
	}

	/**
	 * @throws AppException
	 */
	public function logout(): void {
		$guardDriver = $this->getConfigDriverFromGuard(
			$this->getCurrentGuard()
		);

		switch ($guardDriver) {
			case 'session':
				Session::unset('user');
				break;
			case 'jwt':
				break;
		}
	}

	/**
	 * Make true format for jwt key
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function trueFormatKey(string $key): string
	{
		return base64_decode(strtr($key, '-_', '+/'));
	}

	public function check(): bool {
		if (!is_null($this->user()) && !empty($this->user())) {
			return true;
		}
		return false;
	}

	/**
	 * Set user to application
	 *
	 * @param Model $user
	 *
	 * @return bool
	 *
	 * @throws AuthenticationException
	 * @throws AppException
	 */
	private function setUserAuth(Model $user): bool
	{
		$this->setObject($user);

		$guardDriver = $this->getConfigDriverFromGuard(
			$this->getCurrentGuard()
		);

		switch ($guardDriver) {
			case 'session':
				Session::set('user', $this->getObject());
				break;
			case 'jwt':
				break;
			default:
				throw new AuthenticationException("Unknown authentication");
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
	 */
	public function guard(string $guard = ""): Authenticatable
	{
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
	protected function setObject(Model $object): void
	{
		$this->object = $object;
	}

	/**
	 * Get current object bound
	 *
	 * @return null|Model
	 */
	protected function getObject(): ?Model
	{
		return $this->object;
	}

	/**
	 * Get configuration model from provider
	 *
	 * @param string $provider
	 *
	 * @return string
	 * @throws AppException
	 */
	protected function getConfigModelFromProvider(string $provider): string
	{
		return config("auth.providers.{$provider}.model");
	}

	/**
	 * Get configuration provider from guard
	 *
	 * @param string $guard
	 *
	 * @return string
	 * @throws AppException
	 */
	protected function getConfigProviderFromGuard(string $guard): string
	{
		return config("auth.guards.{$guard}.provider");
	}

	/**
	 * Get configuration provider from guard
	 *
	 * @param string $guard
	 *
	 * @return string
	 * @throws AppException
	 */
	protected function getConfigDriverFromGuard(string $guard): string
	{
		return config("auth.guards.{$guard}.driver");
	}

	/**
	 * Set model name
	 *
	 * @param string $model
	 *
	 * @return void
	 */
	protected function setModel(string $model): void
	{
		$this->model = $model;
	}

	/**
	 * Get current provider
	 *
	 * @return string
	 */
	protected function getProvider(): string
	{
		return $this->provider;
	}

	/**
	 * Set provider
	 *
	 * @param string $provider
	 *
	 * @return void
	 */
	public function setProvider(string $provider): void
	{
		$this->provider = $provider;
	}

	/**
	 * Set guard
	 *
	 * @param string $guard
	 *
	 * @return void
	 */
	public function setGuard(string $guard): void
	{
		$this->guard = $guard;
	}

	/**
	 * Get default guard config
	 *
	 * @return string
	 * @throws AppException
	 */
	private function getDefaultGuard(): string
	{
		return config('auth.defaults.guard');
	}

	/**
	 * Get current guard config
	 *
	 * @return string
	 */
	public function getCurrentGuard(): string
	{
		return $this->guard;
	}
}
