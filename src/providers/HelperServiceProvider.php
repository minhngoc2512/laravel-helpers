<?php

namespace Ngocnm\LaravelHelpers\providers;

use Illuminate\Support\ServiceProvider;
use Ngocnm\LaravelHelpers\command\AutoDeployMultiServer;

class HelperServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
//        $this->mer
        $this->publishes([
            __DIR__.'/../config/helper.php' => config_path('helper.php'),
        ],'helper_config');
        if ($this->app->runningInConsole()) {
            $this->commands([
                AutoDeployMultiServer::class
            ]);
        }
        if(config('hepler.log_query')==true) {
            \DB::enableQueryLog();
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/helper.php', 'helper'
        );
    }

}