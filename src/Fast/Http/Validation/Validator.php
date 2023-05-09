<?php
namespace Fast\Http\Validation;

use Fast\Http\Request;
use Fast\Traits\Validator\Verify;
use Fast\Http\Exceptions\AppException;

class Validator {

	use Verify;

	protected array $rules = [];

	protected array $customRules = [];

	protected array $customMessages = [];

	protected array $messages = [];

	protected bool $isFailed = false;

	protected array $failedMessages = [];

	protected Request $passable;

	protected string $validationFile = 'validation';

	protected string $current = '';

	const SPECIFIC_SEPARATOR = '.';

	public function rules(): array {
		return $this->rules;
	}

	public function messages(): array {
		return $this->messages;
	}

	public function setValidationFile(string $file): void {
		$this->validationFile = $file;
	}

	public function setRule(string $rule, \Closure $handle, string $message = ''): void {
		$this->customRules[$rule] = $handle;
		$this->customMessages[$rule] = $message;
	}

	public function setRules(array $rules): void {
		$this->rules = $rules;
	}

	public function isFailed(): bool {
		return $this->isFailed;
	}

	public function isSucceeded(): bool {
		return !$this->isFailed();
	}

	public function errors(): array {
		return $this->failedMessages;
	}

	public function setPassable(Request $passable): void {
		$this->passable = $passable;
	}

	public function setMessages(array $messages): void {
		$this->messages = $messages;
	}

	public function isCustom(string $rule): bool {
		return isset($this->customRules[$rule]);
	}

	public function getCustom(string $rule): \Closure {
		return $this->customRules[$rule];
	}

	/**
	 * @throws ValidationException
	 */
	public function makeValidate(Request $request, array $validateRules, array $messages = []): Validator {
		$this->setPassable($request);
		$this->setMessages($messages);

		foreach ($validateRules as $param => $rules) {
			$rules = explode('|', $rules);
			$ruleValue = null;

			foreach ($rules as $rule) {
				if (str_contains($rule, ':')) {
					[$rule, $ruleValue] = explode(':', $rule);
				}

				if (!in_array($rule, $this->rules()) && !$this->isCustom($rule)) {
					throw new ValidationException(" Rule {$rule} is not valid.");
				}

				$this->current = $param;
				$this->verify($rule, $rules, $ruleValue);
			}
		}

		return $this;
	}

	/**
	 * @throws ValidationException
	 */
	public function verify(string $rule, array $rules, $ruleValue): void {
		$value = isset($this->passable->all()[$this->current]) ? $this->passable->all()[$this->current] : null;

		switch (true) {
			case $rule === 'required':
			case $rule === 'number':
			case $rule === 'string':
			case $rule === 'file':
			case $rule === 'image':
			case $rule === 'video':
			case $rule === 'audio':
			case $rule === 'email':
				$this->$rule($value);
				break;
			case $rule === 'min':
			case $rule === 'max':
				$this->$rule($value, $rules, $ruleValue);
				break;
			case $rule === 'unique':
				$this->$rule($value, $ruleValue);
			case $this->isCustom($rule):
				$this->handleCustomRule($rule);
				break;
			default:
				throw new ValidationException("The rule {$rule} is not supported !");
		}

		if (!empty($this->errors())) {
			$this->makeFailed();
		}
	}

	/**
	 * @throws \ReflectionException
	 * @throws AppException
	 */
	public function buildErrorMessage($param, string $rule, array $options = []): string {
		if (is_array($param)) {
			[$param, $type] = $param;
		}
		$declaringMessage = $this->getDeclaringMessage($param, $rule);

		$msg =
			!is_null($declaringMessage)
				? $declaringMessage
				: trans(
				$this->validationFile . Validator::SPECIFIC_SEPARATOR . $rule . (isset($type)
					? Validator::SPECIFIC_SEPARATOR . $type
					: ''),
				array_merge($options, [
					'attribute' => $param,
				])
			);

		return is_string($msg) ? $msg : json_encode($msg);
	}

	/**
	 * Get isset error messages registered
	 *
	 * @param string $param
	 * @param string $rule
	 *
	 * @return string|null
	 */
	public function getDeclaringMessage(string $param, string $rule): ?string {
		$currentMessageKey = $param . Validator::SPECIFIC_SEPARATOR . $rule;
		return $this->messages[$currentMessageKey] ?? null;
	}

	/**
	 * Make this request is failed
	 *
	 * @return void
	 */
	public function makeFailed(): void {
		$this->isFailed = true;
	}

	/**
	 * Push to global error messages
	 *
	 * @param string $key
	 * @param string $message
	 *
	 * @return void
	 */
	public function pushErrorMessage(string $key, string $message): void {
		$this->failedMessages[$key][] = $message;
	}
}