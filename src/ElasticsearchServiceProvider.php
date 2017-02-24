<?php

namespace Basemkhirat\Elasticsearch;

use Basemkhirat\Elasticsearch\Commands\CreateIndexCommand;
use Basemkhirat\Elasticsearch\Commands\DropIndexCommand;
use Basemkhirat\Elasticsearch\Commands\UpdateIndexCommand;
use Elasticsearch\ClientBuilder as ElasticBuilder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use Basemkhirat\Elasticsearch\Commands\ListIndicesCommand;

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
            $this->path . '/config/es.php', 'es'
        );

        $this->publishes([
            $this->path . '/config/' => config_path(),
        ], "es.config");

        // Auto configuration with lumen framework.

        if (str_contains($this->app->version(), 'Lumen')) {
            $this->app->configure("es");
        }

        // Resolve Laravel Scout engine.

        if (class_exists("Laravel\\Scout\\EngineManager")) {

            try {

                $this->app->make(EngineManager::class)->extend('es', function () {

                    $config = config('es.connections.' . config('scout.es.connection'));

                    return new ScoutEngine(
                        ElasticBuilder::create()->setHosts($config["servers"])->build(),
                        $config["index"]
                    );

                });

            } catch (BindingResolutionException $e) {

                // Class is not resolved.
                // Laravel Scout service provider was not loaded yet.

            }

        }


    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        if ($this->app->runningInConsole()) {

            // Registering commands

            $this->commands([
                ListIndicesCommand::class,
                CreateIndexCommand::class,
                UpdateIndexCommand::class,
                DropIndexCommand::class
            ]);

        }

        $this->app->bind('es', function () {
            return new Connection();
        });

    }
}
