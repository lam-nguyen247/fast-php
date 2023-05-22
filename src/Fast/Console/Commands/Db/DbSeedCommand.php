<?php

namespace Fast\Console\Commands\Db;

use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class DbSeedCommand extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected string $signature = 'db:seed';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected string $description = 'Run seeding data database';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Handle the command
	 *
	 * @return void
	 * @throws AppException
	 */
	public function handle(): void {
		$files = scandir(database_path('seed'), 1);
		arsort($files);
		foreach ($files as $file) {
			if (strlen($file) > 5) {
				include database_path("seed/{$file}");
				$classes = get_declared_classes();
				$class = end($classes);
				$object = new $class;
				if (method_exists($object, 'run')) {
					$this->output->printSuccess("Running seed: $class");
					$object->run();
					$this->output->printSuccess("Ran seed: $class");
				}
			}
		}
		$this->output->printSuccess('Seeded successfully.');
	}
}
