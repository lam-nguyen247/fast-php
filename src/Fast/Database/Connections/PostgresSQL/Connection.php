<?php
namespace Fast\Database\Connections\PostgresSQL;

use Fast\Http\Exceptions\AppException;
use Fast\Database\Connections\PostgresSQL\PostgresPdo;
use Fast\Database\Connections\Connection as FastConnection;
use Fast\Database\Connections\PostgresSQL\PostgresConnectionException;

class Connection extends FastConnection {
	/**
	 * @throws PostgresConnectionException
	 * @throws AppException
	 */
	public function setDriver(string $driver): void {
		$connections = config('database.connections');
		if (!isset($connections[$driver])) {
			throw new PostgresConnectionException("Could not find driver {$driver}");
		}
		$this->driver = $driver;
		$this->makeInstance();
	}

	/**
	 * Check the connection is available
	 * @return boolean
	 * @throws AppException
	 */
	public function isConnected(): bool {
		try {
			[$driver, $host, $port, $database, $username, $password] = $this->getConfig();
			new PostgresPdo("$driver:host=$host;port=$port;dbname=$database", $username, $password, null);
			return true;
		} catch (\PDOException $e) {
			new PostgresConnectionException($e->getMessage());
			return false;
		}
	}

	public function makeInstance(): void {
		try {
			[$driver, $host, $port, $database, $username, $password] = $this->getConfig();
			$pdo = new PostgresPdo("$driver:host=$host;port=$port;dbname=$database", $username, $password, null);
			$pdo->exec("set names utf8");
			$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
			$pdo->setAttribute(\PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->instance = $pdo;
		} catch (\PDOException $e) {
			throw new PostgresConnectionException($e->getMessage());
		}
	}
}