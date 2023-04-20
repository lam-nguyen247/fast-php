<?php
namespace Fast\Http;

use ReflectionException;
use Fast\Http\Validation\Validator;
use Fast\Http\Exceptions\AppException;
use Fast\Http\Validation\ValidationException;
use Fast\Http\Exceptions\UnauthorizedException;

abstract class FormRequest extends  Request
{
	public function __construct() {
		parent::__construct();
	}

	abstract public function authorize(): bool;

	abstract public function  rules(): array;

	abstract public function messages(): array;

	/**
	 * @throws UnauthorizedException
	 * @throws AppException|ReflectionException
	 */
	public function executeValidate(): void{
		if(!$this->authorize()){
			throw new UnauthorizedException();
		}

		$validator = app()->make('validator');
		$validator->make(
			$this,
			$this->rules(),
			$this->messages()
		);

		if($validator->isFailed()){
			throw new ValidationException($validator->errors());
		}
	}
}