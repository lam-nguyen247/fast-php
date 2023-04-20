<?php

namespace Fast\Console\Commands\Make;

use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class MakeMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $signature = 'make:migration';

    /**
     * The console migration description.
     *
     * @var string
     */
    protected string $description = 'Making migration service';

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
        $table = $this->getOption('table');

        if(empty($table)) {
            $this->output->printError("You're missing argument 'table'. Please using correct format.\n>> make:migration --table={table_name}");
            exit(1);
        }

        $defaultMigratePath = base_path('vendor/faker/faker/src/Fast/Helpers/Init/migrate.txt');
        $defaultMigrate = file_get_contents($defaultMigratePath);
        $defaultMigrate = str_replace(':table', $table, $defaultMigrate);
        $defaultMigrate = str_replace(':Table', ucfirst($table), $defaultMigrate);
        $fullDir = database_path('migration/');
        $date = date('Ymd_His');
        $name = "{$date}_{$table}_migration.php";
        $needleTable = "{$fullDir}$name";
        if (!file_exists($needleTable)) {
            $file = fopen($needleTable, "w") or die("Unable to open file!");
            fwrite($file, $defaultMigrate);
            fclose($file);
            $this->output->printSuccess("Created migration {$name}");
        } else {
            $this->output->printWarning("Table {$needleTable} already exists");
        }
    }
}
