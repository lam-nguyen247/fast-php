<?php

namespace Fast\Contracts\Bus;

interface Dispatcher {
	/**
	 * Dispatch a command to its appropriate handler.
	 *
	 * @param mixed $job
	 * @return mixed
	 */
	public function dispatch(\Fast\Queues\Queue $job): mixed;
}