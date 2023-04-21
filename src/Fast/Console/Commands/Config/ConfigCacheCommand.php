<?php

namespace Fast\Console\Commands\Config;

use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;
use Fast\Console\Commands\View\ViewClearCommand;
use Fast\Console\ConsoleException;
use Fast\Console\Kernel;

class ConfigCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $signature = 'config:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Clear and rewrite caching, views, config';

    /**
     * Flag check using cache
     * @var boolean
     */
    protected bool $usingCache = false;

    /**
     * Other called signatures
     */
    protected array $otherSignatures = [
        'c:c'
    ];

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
	 *
	 * @throws ConsoleException
	 * @throws AppException
	 */
    public function handle(): void
    {
        (new ConfigClearCommand)->handle();

        (new Kernel)->handle([
            (new ViewClearCommand)->getSignature()
        ]);
    }
}