<?php
namespace Fast\Database\Connections\Mysql;

use Fast\Http\Exceptions\AppException;
use Fast\Database\Connections\Connection as FastConnection;
use Fast\Database\Connections\Mysql\MysqlConnectionException;
use PDO;
use PDOException;

class Connection extends FastConnection {
	/**
	 * @throws MysqlConnectionException
	 * @throws AppException
	 */
	public function setDriver(string $driver): void {
		$connections = config('database.connections');
		if (!isset($connections[$driver])) {
			throw new MysqlConnectionException(" Could not find driver {$driver}");
		}
		$this->driver = $driver;
		$this->makeInstance();
	}

	public function isConnected(): bool {
		try {
			[$driver, $host, $port, $database, $username, $password] = $this->getConfig();
			new MysqlPdo("$driver:host=$host;port=$port;dbname=$database", $username, $password, null);
			return true;
		} catch (PDOException $e) {
			new MysqlConnectionException($e->getMessage());
			return false;
		}
	}

	/**
	 * Make instance
	 *
	 * @return void
	 *
	 * @throws MysqlConnectionException|AppException
	 */
	public function makeInstance(): void {
		try {
			[$driver, $host, $port, $database, $username, $password] = $this->getConfig();
			$pdo = new MysqlPdo("$driver:host=$host;port=$port;dbname=$database", $username, $password, null);
			$pdo->exec("set names utf8");
			$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->instance = $pdo;
		} catch (PDOException $e) {
			throw new MysqlConnectionException($e->getMessage());
		}
	}
}