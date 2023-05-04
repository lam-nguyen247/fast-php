<?php

namespace Fast\Http;

use Fast\Services\File;
use Fast\Enums\MethodType;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Auth;
class Request extends SymfonyRequest
{
	public function __construct() {
		foreach ($this->getRequest() as $key => $value){
			$this->$key = $value;
		}
		foreach ($_FILES as $key => $value){
			$this->$key = new File($value);
		}
	}

	public function getRequest(): array
	{
		$params = array_merge($_REQUEST, array_map(function($file){
			return new File($file);
		}, $_FILES));

		if($this->method() === MethodType::PUT){
			parse_str(file_get_contents('php://input'), $data);
			$params = array_merge($params, $data);
		}

		return $params;
	}

	public function all(): array
	{
		return $this->getRequest();
	}

	public function getQueryParams(): array
	{
		return $_GET;
	}

	public function input(string $input) : mixed
	{
		return $this->getRequest()[$input] ?? null;
	}

	public function get(string $input) : mixed
	{
		return $this->input($input);
	}

	public function only(array $inputs): object
	{
		$request = [];
		foreach ($this->getRequest() as $name => $value) {
			if(in_array($name, $inputs)) {
				$request[$name] = $value;
			}
		}

		return (object)$request;
	}

	public function except(array $inputs): object
	{
		$request = [];
		foreach ($this->getRequest() as $name => $value) {
			if(!in_array($name, $inputs)) {
				$request[$name] = $value;
			}
		}

		return (object)$request;
	}

	public function headers(): object
	{
		return (object)getallheaders();
	}

	public function user(?string $guard = null): ?object
	{
		if(is_null($guard)){
			$guard = Auth::getCurrentGuard();
		}

		return Auth::guard($guard)->user();
	}

	/**
	 * Return request is ajax request
	 *
	 * @return bool
	 */
	public function isAjax(): bool {
		$headers = (array) $this->headers();
		return isset($headers['Accept'])
			&& $headers['Accept'] == 'application/json'
			|| isset($headers['Content-Type'])
			&& $headers['Content-Type'] == 'application/json'
			|| isset($headers['x-requested-with'])
			&& $headers['x-requested-with'] == 'XMLHttpRequest';
	}

	/**
	 * Get request server
	 */
	public function server(): array {
		return $_SERVER;
	}

	/**
	 * Get method
	 */
	public function method(): string
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	private array $properties = [];

	public function __set($name, $value) {
		$this->properties[$name] = $value;
	}

	public function __get($name) {
		return $this->properties[$name] ?? null;
	}

	/**
	 * Create an Illuminate request from a Symfony instance.
	 *
	 * @param SymfonyRequest $request
	 * @return static
	 */
	public static function createFromBase(SymfonyRequest $request): static {
		$newRequest = (new static)->duplicate(
			$request->query->all(), $request->request->all(), $request->attributes->all(),
			$request->cookies->all(), $request->files->all(), $request->server->all()
		);

		$newRequest->headers->replace($request->headers->all());

		$newRequest->content = $request->content;

		if ($newRequest->isJson()) {
			$newRequest->request = $newRequest->json();
		}

		return $newRequest;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return static
	 */
	public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null): static
	{
		return parent::duplicate($query, $request, $attributes, $cookies, $this->filterFiles($files), $server);
	}

	/**
	 * Filter the given array of files, removing any empty values.
	 *
	 * @param  mixed  $files
	 * @return mixed
	 */
	protected function filterFiles(mixed $files): mixed {
		if (! $files) {
			return;
		}

		foreach ($files as $key => $file) {
			if (is_array($file)) {
				$files[$key] = $this->filterFiles($file);
			}

			if (empty($files[$key])) {
				unset($files[$key]);
			}
		}

		return $files;
	}
}