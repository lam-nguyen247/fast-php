<?php

namespace Fast\Console\Commands\Key;

use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class KeyGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $signature = 'key:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Generate application key';

    /**
     * Flag check using cache
     * @var boolean
     */
    protected bool $usingCache = false;

    /**
     * Other called signatures
     */
    protected array $otherSignatures = [
        'key:',
        'key:gen',
        'key:genera',
        'gen:key',
        'generate:key'
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
	 * @throws AppException
	 */
    public function handle(): void
    {
        $env = base_path('.env');
        $file_contents = file_get_contents($env);
        $each = explode("\n", $file_contents);
        $file = fopen($env, 'w');
        for ($i = 0; $i <= count($each) - 1; $i++) {
            if ($i == count($each) - 1) {
                if (strlen($each[$i]) <= 0) {
                    continue;
                }
            }
            $value = $each[$i];
            if (str_contains($value, 'APP_KEY')) {
                $value = 'APP_KEY=' . password_hash(microtime(true), PASSWORD_BCRYPT);
            }
            fwrite($file, $value . "\n");
        }
        fclose($file);
        $this->output->printSuccess("Generate key successfully.");
    }
}
