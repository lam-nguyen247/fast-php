## Installation

```bash
   composer create-project lnguyen24794/fast-php:dev-master your-project-folder
```

## About fastPHP Framework

fastPHP is a web application framework designed with a focus on speed and efficiency. We believe that development should be a seamless and satisfying experience, where coding is a joy, not a burden. Our goal is to streamline the web development process by simplifying common tasks that are required in most web projects, such as:

fastPHP is a powerful yet accessible framework that provides all the necessary tools for building large and robust applications. With its elegant and innovative design, it combines simplicity and functionality to give you the tools you need to create any type of web application you can imagine. Whether you're working on a personal project or a complex enterprise application, fastPHP makes the process fast, efficient, and enjoyable.

## Features from fastPHP Framework

_Require PHP Version >= `8.0`_

Let's run `php creator list` to see all available supported commands. Here is some available feature.

**You're wanna making some things ?**

```bash
   php creator make:command {Command name}
   php creator make:controller {Controller name}
   php creator make:model {Model name}
   php creator make:request {Request name}
   php creator make:migration --table={Table name}
```

**Or just refresh caching ?**

```bash
   php creator config:cache
```

**Generate application key !**

```bash
   php creator key:generate
```

or install `Json Web Tokens` for the application ?

```bash
   php creator jwt:install
```

> Then remember refresh caching to register new application key !

**Run migration ?**
so easy

```bash
   php creator migrate
```

or just rollback all of them

```bash
   php creator migrate:rollback
```

**Let's run the seeder**

```bash
   php creator db:seed
```

**Live run query, why not ?**

```bash
   php creator exec:query --query="select * from users"
```

You just make a test ? Ok please give --test=true, like:

```bash
   php creator exec:query --query="select * from users" --test=true
```

**You don't know list of your defined route ?**

```bash
   php creator route:list
```

Or view under `json` or `array`

```bash
   php creator route:list --format=json/array
```

**And of course, you can run live code with creator**
_Code with terminal like with a file_

```bash
   php creator live:code
```

Give helper

_Don't be worry, we're known that, please choose your command and give argument **--help** to get a cup of coffee_

> Here is example: `php creator serve --help`

## Task Scheduling

Just add to your crontab

`* * * * * cd fastPHP && php creator schedule:run >> /dev/null 2>&1`

Example using in `App\Console\Kernel`

```php
<?php

namespace App\Console;

use App\Console\Commands\ExampleCommand;
use Fast\Console\Kernel as ConsoleKernel;
use Fast\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    /**
     * List of commands
     * @var array $commands
     */
    protected array $commands = [
        ExampleCommand::class
    ];

    public function schedule(Schedule $schedule): void
    {
        // Normal using
        $schedule->command(ExampleCommand::class)->daily();
        $schedule->command(ExampleCommand::class)->weekly();
        $schedule->command(ExampleCommand::class)->monthly();
        $schedule->command(ExampleCommand::class)->yearly();
        $schedule->command(ExampleCommand::class)->dailyAt('13:30');
        $schedule->command(ExampleCommand::class)->cron('* * * * *');

        // Run with custom output log and cli
        $schedule->command(ExampleCommand::class)
               ->everyMinute()
               ->output(storage_path('logs/schedule.log'))
               ->cli('/usr/bin/php');
    }
}
```

## How to start ?

```bash
cp .env.example .env
   php creator key:generate
   php creator config:cache
   php creator serve
```

or run with ip and port custom

```bash
   php creator serve --host=192.168.1.1 --port=1997
```

_Note: you can use argument --open to open it up on browser_

> Now your app is running at [127.0.0.1:8000]127.0.0.1:8000

## License

The fastPHP Framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

So, this is the fake framework from the laravel framework idea.

If you want to become contributor, let's run:

```bash
   php creator development:enable
```
