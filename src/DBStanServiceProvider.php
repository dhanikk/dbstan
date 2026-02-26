<?php

namespace Itpathsolutions\DBStan;

use Illuminate\Support\ServiceProvider;
use Itpathsolutions\DBStan\Commands\DBStanAnalyze;

class DBStanServiceProvider extends ServiceProvider {

    public function boot()
    {

        $this->mergeConfigFrom(
            __DIR__.'/../config/dbstan.php',
            'dbstan'
        );
        if ($this->app->environment(['local', 'staging'])) {

            $this->loadRoutesFrom(__DIR__.'/routes/web.php');
            $this->loadViewsFrom(__DIR__.'/resources/views', 'dbstan');
        }

        // Vendor publish for config
        $this->publishes([
            __DIR__ . '/../config/dbstan.php' => config_path('dbstan.php'),
        ], 'dbstan-config');

        // php artisan vendor:publish --tag=dbstan-config

        if ($this->app->environment('production')) {
            if ($this->app->runningInConsole()) {
                $this->commands([
                    \Itpathsolutions\DBStan\Commands\DBStanAnalyze::class,
                ]);
            }
        }
    }
}
?>