<?php

namespace Fast\Console\Commands\Exec;

use PDOException;
use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class ExecQueryCommand extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected string $signature = 'exec:query';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected string $description = 'Run query live';

	/**
	 * Option required
	 *
	 * @var array
	 */
	protected array $required = ['query'];

	/**
	 * True format for command
	 *
	 * @var string
	 */
	protected string $format = '>> fast exec:query --query="{select sql}"';

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
	 *
	 * @throws PDOException|AppException
	 */
	public function handle(): void {
		$query = $this->getOption('query');

		try {
			$connection = app('connection')->getConnection();

			$start = microtime(true);

			if (explode(' ', $query)[0] != 'select') {
				$this->output->printError('Only execute SELECT SQL');
				exit(1);
			}

			$statement = $connection->query($query);

			$touched = $statement->rowCount();

			$result = $this->getOption('test') == 'true' ? '' : json_encode($statement->fetchAll());

			$end = microtime(true);

			$execution_time = (float)($end - $start);

			$execution_time = number_format((float)$execution_time, 10);

			$this->output->print($result);

			$this->output->print("Ran query: $query");
			$this->output->print("Ran time: " . $execution_time . ' seconds');
			$this->output->print("Touched object: $touched");
		} catch (\PDOException $e) {
			$this->output->printError($e->getMessage());
		}
	}
}
