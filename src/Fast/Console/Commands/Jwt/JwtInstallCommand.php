<?php

namespace Fast\Console\Commands\Jwt;

use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class JwtInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $signature = 'jwt:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Json web tokens management';

    /**
     * True format for command
     * 
     * @var string
     */
    protected string $format = 'Please use jwt:install to install secret key for JWT token';

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
            if (str_contains($value, 'JWT_SECRET')) {
                $value = 'JWT_SECRET=' . generateRandomString(20);
            }
            fwrite($file, $value . "\n");
        }
        fclose($file);
        $this->output->printSuccess("Generated secret key for JWT (Json web tokens) successfully.");
    }
}
