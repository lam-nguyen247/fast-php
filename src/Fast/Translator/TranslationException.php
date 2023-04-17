<?php
namespace Fast\Translator;

use Throwable;
use Fast\Http\Exceptions\AppException;

class TranslationException extends AppException
{
	public function __construct($message = "", $code = 500, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}