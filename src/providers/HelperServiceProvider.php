<?php

namespace Ngocnm\LaravelHelpers\providers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Ngocnm\LaravelHelpers\command\AutoDeployMultiServer;
use Ngocnm\LaravelHelpers\command\BackupDatabase;
use Ngocnm\LaravelHelpers\exceptions\Handler;

class HelperServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/helper.php' => config_path('helper.php'),
            __DIR__ . '/../migrations/create_backup_files_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_backup_files_table.php'),
            __DIR__ . '/../models/BackupFile.php' => app_path('Models/BackupFile.php'),
        ], 'helper_config');
        if ($this->app->runningInConsole()) {
            $this->commands([
                AutoDeployMultiServer::class,
                BackupDatabase::class,
            ]);
        }
        if (env('HELPER_LOG_QUERY') == true) {
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
            __DIR__ . '/../config/helper.php', 'helper'
        );
//        if (config('helper.log.enable')){
//            $this->app->singleton(ExceptionHandler::class, Handler::class);
//        }
    }

}