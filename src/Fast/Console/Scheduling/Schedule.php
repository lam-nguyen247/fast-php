<?php

namespace Fast\Console\Scheduling;

use Fast\Console\ConsoleException;
use Fast\Container;
use Fast\Http\Exceptions\AppException;

class Schedule {
	/**
	 * @var Container
	 */
	protected Container $app;
	/**
	 * List of crontab entries
	 *
	 * @var array
	 */
	protected array $tasks = [];

	/**
	 * Signature of command line
	 *
	 * @var string
	 */
	protected string $command = "";

	/**
	 * Expression crontab
	 *
	 * @var string
	 */
	protected string $expression = "";

	/**
	 * Cli php using command
	 *
	 * @var string
	 */
	protected string $cli = 'php';

	/**
	 * Output of command
	 *
	 * @var string
	 */
	protected string $output = "";

	/**
	 * Constructor of Schedule
	 */
	public function __construct() {
		$this->app = Container::getInstance();
	}

	/**
	 * Set command
	 *
	 * @param string $command
	 *
	 * @return self
	 */
	public function command(string $command): Schedule {
		$this->setScheduleAndClear();
		$this->command = $this->app->make($command)->getSignature();
		return $this;
	}

	/**
	 * Set new schedule and clear properties
	 *
	 * @return void
	 */
	public function setScheduleAndClear(): void {
		$this->tasks[] = [
			'command' => $this->getCommand(),
			'expression' => $this->getExpression(),
			'output' => ">> {$this->getOutput()} 2>&1",
			'cli' => $this->getCli(),
		];

		$this->refreshProps();
	}

	/**
	 * Refresh properties
	 *
	 * @return void
	 */
	private function refreshProps(): void {
		$this->command = "";
		$this->expression = "";
		$this->cli = 'php';
		$this->output = "";
	}

	/**
	 * Get command
	 *
	 * @return string
	 */
	private function getCommand(): string {
		return $this->command;
	}

	/**
	 * Get expression
	 *
	 * @return string
	 */
	private function getExpression(): string {
		return $this->expression;
	}

	/**
	 * Get output
	 *
	 * @return string
	 * @throws AppException
	 */
	private function getOutput(): string {
		return !empty($this->output) ? $this->output : storage_path('logs/schedule.log');
	}

	/**
	 * Get cli
	 *
	 * @return string
	 */
	private function getCli(): string {
		return $this->cli;
	}

	/**
	 * Collect all schedule
	 *
	 * @return array
	 */
	public function collect(): array {
		$this->setScheduleAndClear();
		return $this->tasks;
	}

	/**
	 * Set output file for command
	 *
	 * @param string $output
	 *
	 * @return self
	 */
	public function output(string $output): Schedule {
		$this->output = $output;
		return $this;
	}

	/**
	 * Set php cli using command
	 *
	 * @param string $cli
	 *
	 * @return self
	 */
	public function cli(string $cli): Schedule {
		$this->cli = $cli;
		return $this;
	}

	/**
	 * Handle all type of command schedule
	 *
	 * @param string $function
	 * @param array $args
	 *
	 * @return self
	 *
	 * @throws ConsoleException
	 */
	public function __call(string $function, array $args): Schedule {
		try {
			$args = $args[0] ?? null;

			switch ($function) {
				case 'everyMinute':
					$expression = '* * * * *';
					break;
				case 'everyMinutes':
					$expression = "*/{$args} * * * *";
					break;
				case 'hourly':
					$expression = "0 * * * *";
					break;
				case 'hourlyAt':
					$expression = "{$args} * * * *";
					break;
				case 'everyHours':
					$expression = "0 */{$args} * * *";
					break;
				case 'daily':
					$expression = "0 0 * * *";
					break;
				case 'dailyAt':
					if (strpos($args, ':') !== false) {
						[$hour, $min] = explode(':', $args);
						$expression = "{$min} {$hour} * * *";
					} else {
						$expression = "0 $args * * *";
					}
					break;
				case 'weekly':
					$expression = "0 0 * * 0";
					break;
				case 'monthly':
					$expression = "0 0 1 * *";
					break;
				case 'yearly':
					$expression = "0 0 1 1 *";
					break;
				case 'cron':
					$expression = $args;
					break;
				default:
					throw new ConsoleException("Method {$function} does not exist");
			}

			$this->expression = $expression;

			return $this;
		} catch (ConsoleException $e) {
			throw new ConsoleException($e->getMessage());
		}
	}
}
