<?php

namespace Fast\Console\Commands\Config;

use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class ConfigClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $signature = 'config:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Clear and rewrite config, caching';

    /**
     * Flag check using cache
     * @var boolean
     */
    protected bool $usingCache = false;

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
        $this->execClearCache();
        $this->writeEnvironmentCache();
        $this->execWriteConfigCache();
        $this->output->printSuccess("Configuration cleared successfully!");
    }

	/**
	 * Clear cache
	 *
	 * @return void
	 * @throws AppException
	 */
    public function execClearCache(): void
    {
        $path = cache_path();

        if (!is_dir($path)) {
            mkdir($path);
        }
        $items = items_in_folder($path);

        foreach ($items as $file) {
            if(\file_exists($file)) {
                unlink($file);
            }
        }
    }

	/**
	 * Write the caching
	 *
	 * @return void
	 * @throws AppException
	 */
    public function writeEnvironmentCache(): void
    {
        $env = readDotENV();
        $cacheEnvironmentPath = cache_path('environments.php');

        $myFile = fopen($cacheEnvironmentPath, "w") or die("Unable to open file!");
        fwrite($myFile, "<?php\n");
        fwrite($myFile, "return array(\n");
        foreach ($env as $key => $value) {
            $key = trim($key);
            $value = trim($value);
            fwrite($myFile, "    '{$key}' => '{$value}',\n");
        }
        fwrite($myFile, ");");
    }

	/**
	 * Write config caching
	 *
	 * @return void
	 * @throws AppException
	 */
    public function execWriteConfigCache(): void
    {
        $cachePath = cache_path();
        $configPath = config_path();
        $items = items_in_folder($configPath);
        foreach ($items as $file) {
            $config = include $file;
            $file = str_replace($configPath, '', $file);
            $myFile = fopen($cachePath . $file, "w") or die("Unable to open file!");
            fwrite($myFile, "<?php\n");
            fwrite($myFile, "return array(\n");
            foreach ($config as $key => $value) {
                if (is_array($value)) {
                    $this->_handleArrayConfig($key, $myFile, $value);
                } else {
                    fwrite($myFile, "'$key' => '{$value}',\n");
                }
            }
            fwrite($myFile, ");");
        }
    }

    /**
     * Handle array config
     * 
     * @param string $key
     * @param mixed $myfile
     * @param array $values
     * 
     * @return void
     */
    public function _handleArrayConfig(string $key, $myfile, array $values): void
    {
        fwrite($myfile, "'{$key}' => array(\n");
        foreach ($values as $k => $v) {
            if (is_array($v)) {
                $this->_handleArrayConfig($k, $myfile, $v);
            } else {
                fwrite($myfile, "        '{$k}' => '{$v}',\n");
            }
        }
        fwrite($myfile, "    ),\n");
    }
}
