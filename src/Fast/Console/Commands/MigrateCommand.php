<?php

namespace Fast\Console\Commands;

use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class MigrateCommand extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected string $signature = 'migrate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected string $description = 'Run migration database tables';

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
		foreach (scandir(database_path('migration'), 0) as $file) {
			if (strlen($file) > 5) {
				include database_path("migration/{$file}");
				$classes = get_declared_classes();
				$class = end($classes);
				$object = new $class;
				if (method_exists($object, 'up')) {
					$this->output->printSuccess("Migrating: $class");
					$object->up();
					$this->output->printSuccess("Migrated: $class");
				}
			}
		}
	}
}
