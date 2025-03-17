<?php

namespace Noki\WeatherDataProvider;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class WeatherDataProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if (Storage::exists(__DIR__.'/../vendor/autoload.php'))
        {
            include __DIR__.'/../vendor/autoload.php';
        }

        $this->publishes([
            __DIR__.'/config' => config_path('vendor/noki/weather-data-provider'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/app.php', 'package.Noki.WeatherDataProvider.app'
        );

    }
}
