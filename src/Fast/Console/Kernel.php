<?php

namespace Fast\Console;

use Fast\Container;
use Fast\Application;
use ReflectionException;
use Fast\Supports\ConsoleOutput;
use Fast\Console\ConsoleException;
use Fast\Http\Exceptions\AppException;
use Fast\Console\Commands\ListOfCommand;
use Fast\Console\Commands\MigrateCommand;
use Fast\Console\Commands\Db\DbSeedCommand;
use Fast\Console\Commands\Live\LiveCodeCommand;
use Fast\Console\Commands\Exec\ExecQueryCommand;
use Fast\Console\Commands\Jwt\JwtInstallCommand;
use Fast\Console\Commands\Make\MakeModelCommand;
use Fast\Console\Commands\View\ViewClearCommand;
use Fast\Console\Commands\CreateServerCliCommand;
use Fast\Console\Commands\Key\KeyGenerateCommand;
use Fast\Console\Commands\Queue\QueueWorkCommand;
use Fast\Console\Commands\Route\RouteListCommand;
use Fast\Console\Commands\Make\MakeCommandCommand;
use Fast\Console\Commands\Make\MakeRequestCommand;
use Fast\Console\Commands\Queue\QueueTableCommand;
use Fast\Console\Commands\Config\ConfigCacheCommand;
use Fast\Console\Commands\Config\ConfigClearCommand;
use Fast\Console\Commands\Make\MakeMigrationCommand;
use Fast\Contracts\Console\Kernel as KernelContract;
use Fast\Console\Commands\Make\MakeControllerCommand;
use Fast\Console\Commands\Storage\StorageLinkCommand;
use Fast\Console\Commands\Schedule\ScheduleRunCommand;
use Fast\Console\Commands\Migrate\MigrateRollbackCommand;
use Fast\Console\Commands\Development\SyncCoreToFramework;
use Fast\Console\Commands\Development\DevelopmentModeCommand;

class Kernel implements KernelContract {
	/**
	 * @var Container
	 */
	protected Container $app;

	/**
	 * Console output
	 *
	 * @var ConsoleOutput
	 */
	protected ConsoleOutput $output;

	/**
	 * Argv of shell
	 *
	 * @var array
	 */
	protected array $argv;

	/**
	 * List of after run application commands
	 *
	 * @var array
	 */
	protected array $commands = [];

	/**
	 * Instance of the application
	 *
	 * @var null|Application
	 */
	protected ?Application $application;

	/**
	 * Framework type
	 *
	 * @var string
	 */
	const FRAMEWORK_TYPE = 'creator';

	/**
	 * List of application commands
	 *
	 * @var array
	 */
	protected array $appCommands = [
		MigrateCommand::class,
		MigrateRollbackCommand::class,
		CreateServerCliCommand::class,
		MakeCommandCommand::class,
		MakeControllerCommand::class,
		MakeMigrationCommand::class,
		MakeModelCommand::class,
		MakeRequestCommand::class,
		KeyGenerateCommand::class,
		ScheduleRunCommand::class,
		RouteListCommand::class,
		QueueTableCommand::class,
		JwtInstallCommand::class,
		QueueWorkCommand::class,
		DbSeedCommand::class,
		ExecQueryCommand::class,
		LiveCodeCommand::class,
		ListOfCommand::class,
		ConfigCacheCommand::class,
		ViewClearCommand::class,
		ConfigClearCommand::class,
		StorageLinkCommand::class,
		DevelopmentModeCommand::class,
		SyncCoreToFramework::class,
	];

	/**
	 * Constructor of Kernel
	 * @throws AppException
	 * @throws ConsoleException
	 * @throws ReflectionException
	 */
	public function __construct() {
		global $argv;

		array_shift($argv);

		if (empty($argv)) {
			$argv[] = 'list';
		}

		$this->setArgv($argv);

		$this->app = Container::getInstance();

		$this->output = new ConsoleOutput;

		$this->app->singleton(Application::class, function ($app) {
			return new Application($app);
		});

		$this->application = $this->app->make(Application::class);
	}

	/**
	 * Handle execute command
	 *
	 * @param array $argv
	 * @return void
	 *
	 * @throws AppException
	 * @throws ConsoleException
	 * @throws ReflectionException
	 */
	public function handle(array $argv = []): void {
		$argv = empty($argv) ? $this->argv() : $argv;
		$type = strtolower(array_shift($argv));
		foreach ($this->all() as $command) {

			$command = $this->app->make($command);

			if ($type == $command->getSignature() || in_array($type, $command->getOtherSignatures())) {
				if (!empty($argv)) {
					$command->setOptions(array_values($argv));
				}
				if (isset($command->getOptions()['help']) && $command->getOptions()['help'] === true) {
					$helper = $command->getHelper();
					if ($helper !== '') {
						$message = $helper;
					} else {
						$message = $command->getFormat();
					}
					$this->output->printSuccess($message);
					exit(0);
				}

				if (!$command->isVerified()) {
					if ($command->getFormat() !== '') {
						$message = "You're missing some arguments please follow\n" . $command->getFormat();
					} else {
						$message = "You're missing some arguments when run command " . $command->getDescription();
					}
					$this->output->printWarning($message);
					exit(0);
				}
				if ($command->isUsingCache()) {
					if (!$this->caching()) {
						throw new ConsoleException("You're missing register caching, please run `creator config:cache` first !\n");
					}
					$this->application->run();
				}
				$command->setArgv($this->argv);

				$command->handle();
				exit(1);
			}
		}

		$this->output->printError("Bash {$type} is not supported.");
	}

	/**
	 * Call a single command
	 *
	 * @param string $command
	 * @param array $options
	 * @return void
	 *
	 * @throws AppException
	 * @throws ConsoleException
	 * @throws ReflectionException
	 */
	public function call(string $command, array $options = []): void {
		$command = $this->app->make($command);

		if (!empty($options)) {
			$command->setOptions($options);
		}

		if ($command->isUsingCache()) {
			if (!$this->caching()) {
				throw new ConsoleException("Please generate caching files !");
			}
			$this->application->run();
		}
		$command->getArgv($this->argv);

		$command->handle();
		exit(1);
	}

	/**
	 * Check exists application caching
	 *
	 * @return boolean
	 * @throws AppException
	 */
	public function caching(): bool {
		return cacheExists('app.php');
	}

	/**
	 * Get all command lists
	 *
	 * @return array
	 */
	public function all(): array {
		return [...$this->commands, ...$this->appCommands];
	}

	/**
	 * Get argv
	 *
	 * @return array
	 */
	public function argv(): array {
		return $this->argv;
	}

	/**
	 * Set argv
	 *
	 * @param array $argv
	 *
	 * @return void
	 */
	protected function setArgv(array $argv): void {
		$this->argv = $argv;
	}
}
