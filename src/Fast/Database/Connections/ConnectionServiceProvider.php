<?php
namespace Fast\Database\Connections;

use Fast\ServiceProvider;
use Fast\Http\Exceptions\RuntimeException;

class ConnectionServiceProvider extends ServiceProvider
{
	public function register(): void {
		$this->app->singleton('connection', function(){
			try {
				$default = config('database.default');
				return match (true) {
					$default === 'mysql' => new \Fast\Database\Connections\Mysql\Connection(),
					$default === 'pgsql' => new \Fast\Database\Connections\PostgresSQL\Connection(),
					default => throw new RuntimeException("Fast framework still do not support the driver {$default}"),
				};
			}catch (RuntimeException $e) {
				throw new RuntimeException($e->getMessage());
			}
		});
	}
}