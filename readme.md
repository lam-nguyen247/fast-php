## Installation

```bash
   composer create-project lam.nguyen247/fast-framework:dev-master your-project-folder
```

```bash
   cp .env.example .env
   php creator key:generate
   php creator jwt:install
   php creator config:cache
   php creator migrate
   php creator db:seed
```
_note: Create database before run migrate

or run with ip and port custom

```bash
   php creator serve --host=192.168.1.1 --port=2407
```

## About FastPHP Framework

FastPHP is a web application framework designed with a focus on speed and efficiency. We believe that development should
be a seamless and satisfying experience, where coding is a joy, not a burden. Our goal is to streamline the web
development process by simplifying common tasks that are required in most web projects, such as:

FastPHP is a powerful yet accessible framework that provides all the necessary tools for building large and robust
applications. With its elegant and innovative design, it combines simplicity and functionality to give you the tools you
need to create any type of web application you can imagine. Whether you're working on a personal project or a complex
enterprise application, FastPHP makes the process fast, efficient, and enjoyable.

## Features from FastPHP Framework

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

## How to start ?


_Note: you can use argument --open to open it up on browser_

> Now your app is running at [127.0.0.1:8000]127.0.0.1:8000

## License

The FastPHP Framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
