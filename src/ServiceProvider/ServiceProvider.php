<?php

namespace Cirote\Movimientos\ServiceProvider;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
	public function register()
	{
		$this->register_migrations();

		$this->register_routes();

		$this->register_views();
	}

	public function boot()
	{
		$this->loadTranslationsFrom(__DIR__ . '/../Translations', 'activos');
	}

	private function register_migrations()
	{
		$this->loadMigrationsFrom(__DIR__ . '/../Migrations');
	}

	private function register_routes()
	{
		$this->loadRoutesFrom(__DIR__ . '/../Routes/routes.php');
	}

	private function register_views()
	{
		$this->loadViewsFrom(__DIR__ . '/../Views', 'activos');
	}

}