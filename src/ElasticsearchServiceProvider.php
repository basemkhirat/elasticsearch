<?php

namespace Matchory\Elasticsearch;

use Elasticsearch\ClientBuilder as ElasticBuilder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Scout\EngineManager;
use Matchory\Elasticsearch\Commands\CreateIndexCommand;
use Matchory\Elasticsearch\Commands\DropIndexCommand;
use Matchory\Elasticsearch\Commands\ListIndicesCommand;
use Matchory\Elasticsearch\Commands\ReindexCommand;
use Matchory\Elasticsearch\Commands\UpdateIndexCommand;
use RuntimeException;

use function class_exists;
use function config_path;
use function str_starts_with;
use function version_compare;

/**
 * Class ElasticsearchServiceProvider
 *
 * @package Matchory\Elasticsearch
 */
class ElasticsearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/es.php', 'es'
        );

        $this->publishes([
            __DIR__ . '/config/' => config_path(),
        ], 'es.config');

        // Auto configuration with lumen framework.

        if (Str::contains($this->app->version(), 'Lumen')) {
            $this->app->configure('es');
        }

        // Resolve Laravel Scout engine.
        /** @noinspection ClassConstantCanBeUsedInspection */
        if (class_exists('Laravel\\Scout\\EngineManager')) {
            try {
                $this->app
                    ->make(EngineManager::class)
                    ->extend('es', function () {
                        $connectionName = config('scout.es.connection');
                        $config = config("es.connections.{$connectionName}");
                        $elastic = ElasticBuilder
                            ::create()
                            ->setHosts($config['servers'])
                            ->build();

                        return new ScoutEngine(
                            $elastic,
                            $config['index']
                        );
                    });
            } catch (BindingResolutionException $exception) {
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
    public function register(): void
    {
        // Package commands available for laravel or lumen higher than 5.1
        $version = $this->app->version();

        if (
            version_compare($version, '5.1', '>=') &&
            str_starts_with($version, 'Lumen') &&
            $this->app->runningInConsole()
        ) {
            // Registering commands
            $this->commands([
                ListIndicesCommand::class,
                CreateIndexCommand::class,
                UpdateIndexCommand::class,
                DropIndexCommand::class,
                ReindexCommand::class,
            ]);
        }

        $this->app->singleton('es', function () {
            return new Connection();
        });
    }
}
