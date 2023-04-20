<?php

namespace Fast\Console\Commands\Migrate;

use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class MigrateRollbackCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $signature = 'migrate:rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Rollback the migration database tables';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

	/**
	 * Handle the command
	 *
	 * @return void
	 * @throws AppException
	 */
    public function handle(): void
    {
        $files = scandir(database_path('migration'), 1);
        arsort($files);
        foreach ($files as $file) {
            if (strlen($file) > 5) {
                include database_path("migration/{$file}");
                $classes = get_declared_classes();
                $class = end($classes);
                $object = new $class;
                if (method_exists($object, 'down')) {
                    $this->output->printSuccess("Rolling back: $class");
                    $object->down();
                    $this->output->printSuccess("Rolled back: $class");
                }
            }
        }
    }
}
