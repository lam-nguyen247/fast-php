<?php

namespace Fast\Console\Commands\Development;

use ReflectionException;
use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class DevelopmentModeCommand extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected string $signature = 'development:enable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected string $description = 'Enable development mode';

	/**
	 * Others signature
	 *
	 * @var array
	 */
	protected array $otherSignatures = [
		"dev:mode",
	];

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
	 * @throws AppException|ReflectionException
	 */
	public function handle(): void {
		if (file_exists(base_path('Fast'))) {
			$this->output->printError('The `{fast-php}/fast` directory already exists.');
			exit(1);
		}

		$this->app->make('fileSystem')->link(
			base_path('vendor/fast-php/fast/src/Fast'),
			base_path('Fast')
		);

		$this->output->printSuccess('The [{fast-php}/Fast] directory has been linked.');
	}
}


