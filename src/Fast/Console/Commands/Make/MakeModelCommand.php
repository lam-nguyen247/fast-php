<?php

namespace Fast\Console\Commands\Make;

use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class MakeModelCommand extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected string $signature = 'make:model';

	/**
	 * The console model description.
	 *
	 * @var string
	 */
	protected string $description = 'Making model service';

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
	 */
	public function handle(): void {
		$options = $this->getOptions();

		$name = array_shift($options);

		$passeModel = explode('/', $name);
		$namespace = ';';
		$fullDir = base_path('app/Models/');
		if (count($passeModel) > 1) {
			$model = array_pop($passeModel);
			$namespace = '\\' . implode("\\", $passeModel) . ';';
			foreach ($passeModel as $dir) {
				$fullDir .= "{$dir}";
				if (!is_dir($fullDir)) {
					@mkdir($fullDir, 0777, true);
				}
				$fullDir .= '/';
			}
		} else {
			$model = $name;
		}
		$defaultModelPath = base_path('vendor/fast-php/fast/src/Fast/Helpers/Init/model.txt');
		$defaultModel = file_get_contents($defaultModelPath);
		$defaultModel = str_replace(':namespace', $namespace, $defaultModel);
		$defaultModel = str_replace(':model', $model, $defaultModel);
		$needleModel = "{$fullDir}$model.php";
		if (!file_exists($needleModel)) {
			$file = fopen($needleModel, "w") or die("Unable to open file!");
			fwrite($file, $defaultModel);
			fclose($file);
			$this->output->printSuccess("Created model {$model}");
		} else {
			$this->output->printWarning("Model {$needleModel} already exists");
		}
	}
}
