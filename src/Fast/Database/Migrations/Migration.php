<?php

namespace Fast\Database\Migrations;

abstract class Migration
{
    protected \PDO $connection;

    public function __construct()
    {
    }

    public function getConnection(): \PDO
    {
        return $this->connection;
    }
}
