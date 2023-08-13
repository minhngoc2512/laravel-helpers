<?php

namespace Ngocnm\LaravelHelpers;

use Illuminate\Support\ServiceProvider;

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
            __DIR__.'/config/helper.php' => config_path('helper.php'),
        ],'helper_config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/helper.php', 'helper'
        );
    }

}