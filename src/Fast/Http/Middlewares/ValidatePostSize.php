<?php

namespace Fast\Http\Middlewares;

use Closure;
use Fast\Container;
use Fast\Http\Request;
use Fast\Http\Middlewares\MiddlewareException;

class ValidatePostSize
{
    /**
     * The application implementation.
     *
     * @var Container
     */
    protected Container $app;

    /**
     * Create a new middleware instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->app = Container::getInstance();
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     *
     * @throws MiddlewareException
     */
    public function handle(Request $request, Closure $next): mixed {
        $max = $this->getPostMaxSize();

        if (
            $max > 0
            && $request->server() !== null
            && isset($request->server()['CONTENT_LENGTH'])
            && $request->server()['CONTENT_LENGTH'] > $max
        ) {
            throw new MiddlewareException("Post Body too large, body is {$_SERVER['CONTENT_LENGTH']}");
        }

        return $next($request);
    }

    /**
     * Determine the server 'post_max_size' as bytes.
     *
     * @return int
     */
    protected function getPostMaxSize(): int
    {
        if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
            return (int) $postMaxSize;
        }

        $metric = strtoupper(substr($postMaxSize, -1));

		return match ($metric) {
			'K' => (int)$postMaxSize * 1024,
			'M' => (int)$postMaxSize * 1048576,
			'G' => (int)$postMaxSize * 1073741824,
			default => (int)$postMaxSize,
		};
    }
}
