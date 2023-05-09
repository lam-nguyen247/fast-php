<?php

namespace Fast\Console\Commands\View;

use ReflectionException;
use Fast\Console\Command;
use Fast\Container;
use Fast\Http\Exceptions\AppException;

class ViewClearCommand extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected string $signature = 'view:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected string $description = 'Clear view cache and rewrite';

	/**
	 * Flag is using cache
	 *
	 * @var bool
	 */
	protected bool $usingCache = true;

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
	 * @throws ReflectionException
	 */
	public function handle(): void {
		$view = Container::getInstance()->make('view');
		$directory = $view->getDirectory();
		$cachingDirectory = $view->getCachingDirectory();

		if (is_dir($cachingDirectory)) {
			delete_directory($cachingDirectory);
			mkdir($cachingDirectory);
		}

		$views = items_in_folder($directory, false);

		foreach ($views as $v) {
			$view->makeCache($v);
		}

		$this->output->printSuccess("View cleared successfully.");
	}
}
