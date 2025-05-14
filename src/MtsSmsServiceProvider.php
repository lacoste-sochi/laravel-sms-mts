<?php

namespace Rutrue\MtsSms;

use Illuminate\Support\ServiceProvider;

class MtsSmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mts-sms.php',
            'mts-sms'
        );

        $this->app->singleton('mts-sms', function ($app) {
            return new Services\MtsSmsService(
                config('mts-sms.api_key'),
                config('mts-sms.api_url'),
                config('mts-sms.sender_name')
            );
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/mts-sms.php' => config_path('mts-sms.php'),
            ], 'mts-sms-config');
        }
    }
}
