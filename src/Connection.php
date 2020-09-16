<?php

namespace Matchory\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use RuntimeException;

use function array_key_exists;
use function call_user_func_array;
use function method_exists;

/**
 * Class Connection
 *
 * @package Matchory\Elasticsearch
 */
class Connection
{
    private const DEFAULT_LOGGER_NAME = 'elasticsearch';

    /**
     * Laravel app instance
     *
     * @var Application
     */
    protected $app;

    /**
     * Elastic config content
     *
     * @var array
     */
    protected $config;

    /**
     * The current connection
     *
     * @var Client|null
     */
    protected $client;

    /**
     * all available connections
     *
     * @var Client[]
     */
    protected $clients = [];

    /**
     * TODO: The hard dependency on the `app()` function should be replaced with
     *       dependency injection
     */
    public function __construct()
    {
        $this->app = app();
        $this->config = $this->app['config']['es'] ?? [];
    }

    /**
     * Create a native connection
     * suitable for any non-laravel or non-lumen apps
     * any composer based frameworks
     *
     * @param $config
     *
     * @return Query
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public static function create($config): Query
    {
        $clientBuilder = ClientBuilder::create();

        if ( ! empty($config['handler'])) {
            $clientBuilder->setHandler($config['handler']);
        }

        $clientBuilder->setHosts($config['servers']);

        $clientBuilder = self::configureLogging(
            $clientBuilder,
            $config
        );

        $query = new Query($clientBuilder->build());

        if (
            array_key_exists('index', $config) &&
            $config['index'] !== ''
        ) {
            $query->index($config['index']);
        }

        return $query;
    }

    /**
     * @param ClientBuilder $clientBuilder
     * @param array         $config
     *
     * @return ClientBuilder
     * @throws InvalidArgumentException
     */
    public static function configureLogging(
        ClientBuilder $clientBuilder,
        array $config
    ): ClientBuilder {
        if (Arr::get($config, 'logging.enabled')) {
            $logger = new Logger(self::DEFAULT_LOGGER_NAME);
            $logger->pushHandler(new StreamHandler(
                Arr::get(
                    $config,
                    'logging.location'
                ),
                (int)Arr::get(
                    $config,
                    'logging.level',
                    Logger::INFO
                )
            ));

            $clientBuilder->setLogger($logger);
        }

        return $clientBuilder;
    }

    /**
     * Create a connection for laravel or lumen frameworks
     *
     * @param $name
     *
     * @return Query
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function connection(string $name): Query
    {
        // Check if connection is already loaded.
        if ($this->isLoaded($name)) {
            $this->client = $this->clients[$name];

            return $this->newQuery($this->client);
        }

        // Create a new connection.
        if (array_key_exists($name, $this->config['connections'])) {
            $config = $this->config['connections'][$name];

            // Instantiate a new ClientBuilder
            $clientBuilder = ClientBuilder::create();

            $clientBuilder->setHosts($config['servers']);

            $clientBuilder = self::configureLogging(
                $clientBuilder,
                $config
            );

            if ( ! empty($config['handler'])) {
                $clientBuilder->setHandler($config['handler']);
            }

            // Build the client object
            $this->client = $clientBuilder->build();
            $this->clients[$name] = $this->client;

            return $this->newQuery($this->client);
        }

        throw new RuntimeException(
            "Invalid elasticsearch connection driver '{$name}'"
        );
    }

    /**
     * route the request to the query class
     *
     * @param string $connection
     *
     * @return Query
     */
    public function newQuery(string $connection): Query
    {
        $config = $this->config['connections'][$connection];

        $query = new Query($this->clients[$connection]);

        if (
            $config['index'] !== '' &&
            array_key_exists('index', $config)
        ) {
            $query->index($config['index']);
        }

        return $query;
    }

    /**
     * Check if the connection is already loaded
     *
     * @param string $name
     *
     * @return bool
     */
    public function isLoaded(string $name): bool
    {
        if (array_key_exists($name, $this->clients)) {
            return true;
        }

        return false;
    }

    /**
     * Proxy  calls to the default connection
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array(
                [$this, $name],
                $arguments
            );
        }

        // if no connection, use default.
        $query = $this->connection($this->config['default']);

        return call_user_func_array(
            [$query, $name],
            $arguments
        );
    }

}
