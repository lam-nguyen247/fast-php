<?php
namespace Fast\Database\Connections;

use PDO;
use ReflectionException;
use Fast\Http\Exceptions\AppException;

abstract class Connection implements \Fast\Contracts\Database\Connection
{
	protected string $driver;

	protected PDO $instance;

	/**
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function __construct()
	{
		$this->setDriver($this->getDefaultDriver());
	}

	/**
	 * @throws AppException
	 * @throws ReflectionException
	 */
	public function getConfig(): array
	{
		return [
			$this->getDriverConnection($this->driver()),
			$this->getHostConnection($this->driver()),
			$this->getPortConnection($this->driver()),
			$this->getDatabaseConnection($this->driver()),
			$this->getUsernameConnection($this->driver()),
			$this->getPasswordConnection($this->driver())
		];
	}

	/**
	 * Get driver
	 *
	 * @return string
	 */
	protected function driver(): string
	{
		return $this->driver;
	}

	/**
	 * Get driver connection
	 *
	 * @param string $driver
	 *
	 * @return string
	 * @throws AppException
	 * @throws ReflectionException
	 */
	protected function getDriverConnection(string $driver): string
	{
		return config("database.connections.{$driver}.driver");
	}

	/**
	 * Get host connection
	 *
	 * @param string $driver
	 * @return string
	 * @throws AppException
	 * @throws ReflectionException
	 */
	protected function getHostConnection(string $driver): string
	{
		return config("database.connections.{$driver}.host");
	}

	/**
	 * Get port connection
	 *
	 * @param string $driver
	 *
	 * @return string
	 * @throws AppException
	 * @throws ReflectionException
	 */
	protected function getPortConnection(string $driver): string
	{
		return config("database.connections.{$driver}.port");
	}

	/**
	 * Get database connection
	 *
	 * @param string $driver
	 *
	 * @return string
	 * @throws AppException
	 * @throws ReflectionException
	 */
	protected function getDatabaseConnection(string $driver): string
	{
		return config("database.connections.{$driver}.database");
	}

	/**
	 * Get username connection
	 *
	 * @param string $driver
	 *
	 * @return string
	 * @throws AppException
	 * @throws ReflectionException
	 */
	protected function getUsernameConnection(string $driver): string
	{
		return config("database.connections.{$driver}.username");
	}

	/**
	 * Get password connection
	 *
	 * @param string $driver
	 *
	 * @return string
	 * @throws AppException
	 * @throws ReflectionException
	 */
	protected function getPasswordConnection(string $driver): string
	{
		return config("database.connections.{$driver}.password");
	}

	/**
	 * Get default driver
	 *
	 * @return string
	 * @throws AppException
	 * @throws ReflectionException
	 */
	protected function getDefaultDriver(): string
	{
		return config('database.default');
	}

	/**
	 * Make instance
	 *
	 * @return void
	 */
	abstract function makeInstance(): void;

	/**
	 * Get the connection
	 *
	 * @return PDO
	 */
	public function getConnection(): PDO
	{
		return $this->instance;
	}
}