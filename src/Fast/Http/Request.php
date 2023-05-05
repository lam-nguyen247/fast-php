<?php

namespace Fast\Http;

use Fast\Services\File;
use Fast\Enums\MethodType;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Auth;
class Request
{

	public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null) {
		parent::__construct($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);
	}

	/**
	 * The decoded JSON content for the request.
	 *
	 * @var ParameterBag|null
	 */
	protected ?ParameterBag $json;

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

	public function get(string $key, mixed $default = null) : mixed
	{
		return $this->input($key);
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
		return $this->getMethod();
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
	 * Get the JSON payload for the request.
	 *
	 * @param string|null $key
	 * @param mixed|null $default
	 * @return ParameterBag|mixed
	 */
	public function json(string $key = null, mixed $default = null): mixed {
		if (! isset($this->json)) {
			$this->json = new ParameterBag((array) json_decode($this->getContent(), true));
		}

		if (is_null($key)) {
			return $this->json;
		}

		return data_get($this->json->all(), $key, $default);
	}

	public function isJson(): bool {
		return str_contains($this->header('CONTENT_TYPE') ?? '', '/json') || str_contains($this->header('CONTENT_TYPE') ?? '', '+json');
	}

	/**
	 * Retrieve a header from the request.
	 *
	 * @param string|null $key
	 * @param array|string|null $default
	 * @return string|array|null
	 */
	public function header(string $key = null, array|string $default = null): array|string|null {
		return $this->retrieveItem('headers', $key, $default);
	}

	/**
	 * Retrieve a parameter item from a given source.
	 *
	 * @param string $source
	 * @param string|null $key
	 * @param array|string|null $default
	 * @return string|array|null
	 */
	protected function retrieveItem(string $source, ?string $key, array|string|null $default): array|string|null {
		if (is_null($key)) {
			return $this->$source->all();
		}

		if ($this->$source instanceof InputBag) {
			return $this->$source->all()[$key] ?? $default;
		}
		return $this->$source->get($key, $default);
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
			return null;
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

	public static function getFromServer(string $method){

	}
}
