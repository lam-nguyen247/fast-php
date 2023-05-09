<?php

namespace Fast\Bus;

use DB;
use Fast\Queues\Queue;
use Fast\Bus\DispatcherException;
use Fast\Contracts\Bus\Dispatcher as DispatcherContract;

class Dispatcher implements DispatcherContract {
	/**
	 * Dispatch a command to its appropriate handler.
	 *
	 * @param mixed $job
	 * @return mixed
	 *
	 * @throws DispatcherException
	 */
	public function dispatch(Queue $job): mixed {
		try {
			return DB::table('jobs')->insert([
				'queue' => str_replace('\\', '\\\\', get_class($job)),
				'payload' => json_encode($job->getSerializeData()),
				'attempts' => 0,
			]);
		} catch (\Exception $e) {
			throw new DispatcherException($e->getMessage());
		}
	}
}
