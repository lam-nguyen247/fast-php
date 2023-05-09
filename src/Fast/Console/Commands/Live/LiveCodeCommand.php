<?php

namespace Fast\Console\Commands\Live;

use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class LiveCodeCommand extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected string $signature = 'live:code';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected string $description = 'Live code';

	/**
	 * Other called signatures
	 *
	 * @var array
	 */
	protected array $otherSignatures = [
		'tinker',
		'code',
		'go',
	];

	/**
	 * Path of run temp file
	 *
	 * @var string
	 */
	protected string $temp = "";

	/**
	 * Expression of dd
	 *
	 * @var string
	 */
	const DD = 'dd(';

	/**
	 * Expression of var dump
	 *
	 * @var string
	 */
	const VAR_DUMP = 'var_dump';

	/**
	 * Expression of echo
	 *
	 * @var string
	 */
	const ECHO = 'echo';

	/**
	 * Expression of print
	 *
	 * @var string
	 */
	const PRINT = 'print';

	/**
	 * Expression of exit
	 *
	 * @var string
	 */
	const EXIT = 'exit';

	/**
	 * Expression of ex
	 *
	 * @var string
	 */
	const EX = ';';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 * @throws AppException
	 */
	public function __construct() {
		parent::__construct();

		$this->temp = storage_path('framework/live.temp');

		if (!file_exists($this->temp)) {
			touch($this->temp);
		}
	}

	/**
	 * Handle the command
	 *
	 * @return void
	 */
	public function handle(): void {
		if (!function_exists('readline')) {
			$this->output->printError("You're php environments is not allowed readline(), please make sure it's work.");
			exit(1);
		}

		switch (true) {
			case app()->isWindows():
				@system('cls');
				break;
			default:
				@system('clear');
				break;
		}

		$this->output->print("Welcome to the Creator live code !");
		$this->output->print("Write down your code in console like a file.");

		$this->reWriteTemp();
		while (true) {
			$input = readline(">> ");

			if ($input == self::EXIT || $input == self::EXIT . self::EX) {
				$this->output->print("Goodbye ! See you next time.");
				break;
			}

			$output = file_get_contents($this->temp);

			$output = array_filter(explode(PHP_EOL, $output), function ($line) {
				return !str_contains($line, self::ECHO) && !str_contains($line, self::DD) && !str_contains($line, self::PRINT) && !str_contains($line, self::VAR_DUMP);
			});

			$output[] = $input;

			$this->reWriteTemp(implode(PHP_EOL, $output));

			eval(implode(PHP_EOL, $output));

			if (str_contains($input, self::ECHO)) {
				echo PHP_EOL;
			}
		}
	}

	/**
	 * Write log output
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function writeLog(string $message): void {
		file_put_contents($this->temp, $message);
	}

	/**
	 * Re-write temp file
	 *
	 * @param string $content
	 *
	 * @return void
	 */
	public function reWriteTemp(string $content = ""): void {
		@unlink($this->temp);

		$this->writeLog($content);
	}
}
