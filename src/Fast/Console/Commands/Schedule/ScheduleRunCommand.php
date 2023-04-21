<?php

namespace Fast\Console\Commands\Schedule;

use ReflectionException;
use Fast\Console\Command;
use Fast\Http\Exceptions\AppException;

class ScheduleRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $signature = 'schedule:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Handle task scheduling';

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
	 * @throws ReflectionException
	 */
    public function handle(): void
    {
        error_reporting(0);

        $schedule = $this->app->make(\fast\Console\Scheduling\Schedule::class);

        $kernel = $this->app->make(\fast\Contracts\Console\Kernel::class);

        $kernel->schedule($schedule);

        $tasks = $schedule->collect();

        $tasks = array_filter($tasks, function ($task) {
            return !empty($task['expression']);
        });

        $crontabs = [];

        $base = base_path();

        foreach ($tasks as $task) {
            $crontabs[] = "{$task['expression']} cd {$base} && {$task['cli']} creator {$task['command']} {$task['output']}";
        }

        $cacheFile = storage_path('framework/crontab.txt');

        if (!file_exists($cacheFile)) {
            touch($cacheFile);
        }

        $contentCaching = file_get_contents($cacheFile);

        $currents = array_filter(explode(PHP_EOL, shell_exec('crontab -l')), function ($line) {
            return $line !== '';
        });

        foreach (explode(PHP_EOL, $contentCaching) as $line) {
            $key = array_search($line, $currents);
            if ($key !== false) {
                unset($currents[$key]);
            }
        }
        $currents = array_merge($currents, $crontabs);

        file_put_contents($cacheFile, implode(PHP_EOL, $crontabs));
        file_put_contents('/tmp/crontab.txt', implode(PHP_EOL, $currents) . PHP_EOL);

        exec('crontab /tmp/crontab.txt');

        if (!is_null(error_get_last())) {
            logger()->writeLog(
                'ERROR',
                $this->getLastErrorMsg(),
                storage_path('logs/'),
                'schedule',
                false
            );
        };
    }

    /**
     * Get last error message
     * 
     * @return string
     */
    public function getLastErrorMsg(): string
    {
        return error_get_last()['message'] . ' in ' . error_get_last()['file'] . ' line ' . error_get_last()['line'];
    }
}
