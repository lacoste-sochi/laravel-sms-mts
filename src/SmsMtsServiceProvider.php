<?php

namespace RuTrue\SmsMts;

use Illuminate\Support\ServiceProvider;
use RuTrue\SmsMts\Contracts\SmsMtsDriverInterface;
use RuTrue\SmsMts\Services\SmsMtsService;

class SmsMtsServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->mergeConfigFrom(
			__DIR__ . '/../config/sms-mts.php',
			'sms-mts'
		);

		$this->app->singleton(SmsMtsDriverInterface::class, function ($app) {
			return new SmsMtsService();
		});
	}

	public function boot()
	{
		$this->publishes([
			__DIR__ . '/../config/sms-mts.php' => config_path('sms-mts.php'),
		], 'sms-mts-config');
	}
}
