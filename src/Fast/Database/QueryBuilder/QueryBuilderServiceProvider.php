<?php
namespace Fast\Database\QueryBuilder;

use Fast\ServiceProvider;

class QueryBuilderServiceProvider extends ServiceProvider
{
	public function register(): void {
		$this->app->singleton('db', function () {
			return new QueryBuilder();
		});
	}
}