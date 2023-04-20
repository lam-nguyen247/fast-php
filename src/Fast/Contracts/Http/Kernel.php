<?php

namespace Fast\Contracts\Http;

use Fast\Container;
use Fast\Http\Request;

interface Kernel
{
	/**
	 * Handle an incoming HTTP request.
	 *
	 * @param Request $request
	 * @return void
	 */
    public function handle(Request $request): void;

    /**
     * Get the FastPHP application instance.
     *
     * @return Container
     */
    public function getApplication(): Container;
}
