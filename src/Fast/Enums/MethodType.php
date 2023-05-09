<?php
namespace Fast\Enums;

use Fast\Services\Enum;

class MethodType extends Enum {
	const GET = "GET";
	const POST = "POST";
	const PUT = "PUT";
	const PATCH = "PATCH";
	const DELETE = "DELETE";
	const HEAD = "HEAD";
	const CONNECT = "CONNECT";
	const OPTIONS = "OPTIONS";
	const TRACE = "TRACE";
}