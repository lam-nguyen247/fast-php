<?php

namespace Fast\Console\Commands\Development;

use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class SyncCoreToFramework extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $signature = 'development:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Sync core code to framework folder';

    /**
     * Others signature
     * 
     * @var array
     */
    protected array $otherSignatures = [
        "dev:sync"
    ];

    /**
     * Framework directory
     * 
     * @var string
     */
    protected string $frameworkDir;

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
        $frameworkDir = $this->getOption('dir') ?: config('develop.framework_dir');
        $developDir = base_path('vendor/fast-php/fast/src/Fast');

        if(!file_exists($frameworkDir)) {
        	$this->output->printError("Wrong given framework directory. Please check your configuration.\nThe given directory: `{$frameworkDir}`");
            exit(1);
        }

        exec("rm -rf {$frameworkDir}/src/Fast/");
        exec("cp -R {$developDir} {$frameworkDir}/src/Fast/");

        $this->output->printSuccess("Synced development directory to framework directory!\nPlease commit your code on {$frameworkDir}");
    }
}


