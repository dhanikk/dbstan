<?php

namespace Itpathsolutions\DBStan;

use Illuminate\Support\ServiceProvider;
use Itpathsolutions\DBStan\Commands\DBStanAnalyze;

class DBStanServiceProvider extends ServiceProvider {
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DBStanAnalyze::class,
            ]);
        }
    }
    public function register()
    {
    }
}
?>