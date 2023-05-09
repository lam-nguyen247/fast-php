<?php

namespace Fast\Contracts\Console;

interface Kernel {
	/**
	 * Handle the console command
	 */
	public function handle(): void;

	/**
	 * Get all the commands registered with the console.
	 *
	 * @return array
	 */
	public function all(): array;

	/**
	 * Call a single command
	 *
	 * @param string $command
	 * @param array $options
	 * @return void
	 */
	public function call(string $command, array $options = []): void;
}
