<?php

namespace Fast\Console\Commands\Make;

use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class MakeRequestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $signature = 'make:request';

    /**
     * The console request description.
     *
     * @var string
     */
    protected string $description = 'Making request service';

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
        $options = $this->getOptions();

        $request = array_shift($options);

        $passeRequest = explode('/', $request);
        $namespace = ';';
        $fullDir = base_path('app/Http/Requests/');
        if (count($passeRequest) > 1) {
            $request = array_pop($passeRequest);
            $namespace = '\\' . implode("\\", $passeRequest) . ';';
            foreach ($passeRequest as $dir) {
                $fullDir .= "{$dir}";
                if (!is_dir($fullDir)) {
                    @mkdir($fullDir, 0777, true);
                }
                $fullDir .= '/';
            }
        }
        $defaultRequestPath = base_path('vendor/faker/faker/src/Fast/Helpers/Init/request.txt');
        $defaultRequest = file_get_contents($defaultRequestPath);
        $defaultRequest = str_replace(':request', $request, $defaultRequest);
        $defaultRequest = str_replace(':namespace', $namespace, $defaultRequest);
        $defaultRequest = str_replace(':Request', ucfirst($request), $defaultRequest);
        $name = "{$request}.php";
        $needleRequest = "{$fullDir}$name";
        if (!file_exists($needleRequest)) {
            $file = fopen($needleRequest, "w") or die("Unable to open file!");
            fwrite($file, $defaultRequest);
            fclose($file);
            $this->output->printSuccess("Created Request {$request}");
        } else {
            $this->output->printWarning("Request {$needleRequest} already exists");
        }
    }
}
