<?php

namespace Basemkhirat\Elasticsearch;

use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider
{

    function __construct()
    {
        $this->path = dirname(__FILE__);
        $this->app = app();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->mergeConfigFrom(
            $this->path .'/config/es.php', 'es'
        );

        $this->publishes([
            $this->path . '/config/' => config_path(),
        ], "es.config");

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('es', function () {
            return new Connection();
        });
    }
}