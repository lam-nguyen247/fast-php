<?php

namespace Fast\Console\Commands\Queue;

use Fast\Console\Command;
use Fast\Database\DatabaseBuilder\Schema;
use Fast\Database\DatabaseBuilder\ColumnBuilder;

class QueueTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $signature = 'queue:table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = 'Install queue table';

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
     */
    public function handle(): void
    {
        Schema::create('jobs', function (ColumnBuilder $table) {
            $table->increments('id');
            $table->string('queue');
            $table->longText('payload');
            $table->text('last_error')->nullable();
            $table->tinyInteger('attempts')->unsigned();
            $table->timestamps();
        });
        $this->output->printSuccess("Generated jobs table.");
    }
}
