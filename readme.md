## Installation

```bash
   composer create-project lam.nguyen247/fast-framework:dev-master your-project-folder
```

## How to start

```bash
   cp .env.example .env
   php creator key:generate
   php creator jwt:install
   php creator config:cache
   php creator migrate
   php creator db:seed
```
_note: Create database before run migrate

## About FastPHP Framework

FastPHP is a web application framework designed with a focus on speed and efficiency. We believe that development should be a seamless and satisfying experience, where coding is a joy, not a burden. Our goal is to streamline the web development process by simplifying common tasks that are required in most web projects, such as:

FastPHP is a powerful yet accessible framework that provides all the necessary tools for building large and robust applications. With its elegant and innovative design, it combines simplicity and functionality to give you the tools you need to create any type of web application you can imagine. Whether you're working on a personal project or a complex enterprise application, FastPHP makes the process fast, efficient, and enjoyable.

## Features from FastPHP Framework

_Require PHP Version >= `8.0`_

Let's run `php creator list` to see all available supported commands. Here is some available feature.

**Example command**

```bash
   php creator make:command {Command name}
   php creator make:controller {Controller name}
   php creator make:model {Model name}
   php creator make:request {Request name}
   php creator make:migration --table={Table name}
```

**Refresh caching**

```bash
   php creator config:cache
```

**Generate application key**

```bash
   php creator key:generate
```

**Install `Json Web Tokens` for the application**

```bash
   php creator jwt:install
```

> Then remember refresh caching to register new application key !

**Run migration**

```bash
   php creator migrate
```

Rollback all of them

```bash
   php creator migrate:rollback
```

**Run the seeder**

```bash
   php creator db:seed
```

**Live run query**

```bash
   php creator exec:query --query="select * from users"
```

Make a test. Please give --test=true, like:

```bash
   php creator exec:query --query="select * from users" --test=true
```

**List of your defined route.**

```bash
   php creator route:list
```

View under `json` or `array`

```bash
   php creator route:list --format=json/array
```

