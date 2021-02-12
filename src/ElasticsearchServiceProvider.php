<?php

namespace Matchory\Elasticsearch;

use Elasticsearch\ClientBuilder as ElasticBuilder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Scout\EngineManager;
use LogicException;
use Matchory\Elasticsearch\Commands\CreateIndexCommand;
use Matchory\Elasticsearch\Commands\DropIndexCommand;
use Matchory\Elasticsearch\Commands\ListIndicesCommand;
use Matchory\Elasticsearch\Commands\ReindexCommand;
use Matchory\Elasticsearch\Commands\UpdateIndexCommand;
use Matchory\Elasticsearch\Factories\ClientFactory;
use Matchory\Elasticsearch\Interfaces\ClientFactoryInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionResolverInterface;

use function class_exists;
use function config_path;
use function method_exists;
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
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->configure();

        // Enable automatic connection resolution in all models
        Model::setConnectionResolver($this->app->make(
            ConnectionResolverInterface::class
        ));

        // Enable event dispatching in all models
        Model::setEventDispatcher($this->app->make(
            Dispatcher::class
        ));

        // TODO: Remove in next major version
        Connection::setConnectionResolver($this->app->make(
            ConnectionResolverInterface::class
        ));

        // Register the Laravel Scout Engine
        $this->registerScoutEngine();
    }

    /**
     * Register any application services.
     *
     * @return void
     * @throws LogicException
     */
    public function register(): void
    {
        Model::clearBootedModels();

        $this->registerCommands();
        $this->registerClientFactory();
        $this->registerConnectionResolver();
        $this->registerDefaultConnection();
    }

    protected function configure(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/es.php', 'es');
        $this->publishes([
            __DIR__ . '/../config/' => config_path(),
        ], 'es.config');

        // Auto configuration with lumen framework.
        if (
            method_exists($this->app, 'configure') &&
            Str::contains($this->app->version(), 'Lumen')
        ) {
            $this->app->configure(ConnectionResolverInterface::class);
        }
    }

    protected function registerScoutEngine(): void
    {
        // Resolve Laravel Scout engine.
        if ( ! class_exists(EngineManager::class)) {
            return;
        }

        try {
            $this->app
                ->make(EngineManager::class)
                ->extend('es', function () {
                    $connectionName = Config::get('scout.es.connection');
                    $config = Config::get("es.connections.{$connectionName}");
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

    protected function registerCommands(): void
    {
        $version = $this->app->version();

        if (
            version_compare($version, '5.1', '>=') ||
            Str::startsWith($version, 'Lumen') ||
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
    }

    /**
     * @throws LogicException
     */
    protected function registerClientFactory(): void
    {
        // Bind our default client factory on the container, so users may
        // override it if they need to build their client in a specific way
        $this->app->singleton(
            ClientFactoryInterface::class,
            ClientFactory::class
        );

        $this->app->alias(
            ClientFactoryInterface::class,
            'es.factory'
        );
    }

    /**
     * @throws LogicException
     */
    protected function registerConnectionResolver(): void
    {
        // Bind the connection manager for the resolver interface as a singleton
        // on the container, so we have a single instance at all times
        $this->app->singleton(
            ConnectionResolverInterface::class,
            function (Application $app) {
                $factory = $app->make(ClientFactoryInterface::class);

                return new ConnectionManager(
                    Config::get('es', []),
                    $factory
                );
            }
        );

        $this->app->alias(
            ConnectionResolverInterface::class,
            'es.resolver'
        );

        $this->app->alias(
            ConnectionResolverInterface::class,
            'es'
        );
    }

    /**
     * @throws LogicException
     */
    protected function registerDefaultConnection(): void
    {
        // Bind the default connection separately
        $this->app->singleton(
            ConnectionInterface::class,
            function (Application $app) {
                return $app
                    ->make(ConnectionResolverInterface::class)
                    ->connection();
            }
        );

        $this->app->alias(
            ConnectionInterface::class,
            'es.connection'
        );
    }
}
