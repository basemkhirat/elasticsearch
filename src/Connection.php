<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch;

use BadMethodCallException;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Traits\ForwardsCalls;
use InvalidArgumentException;
use Matchory\Elasticsearch\Interfaces\ClientFactoryInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionResolverInterface as Resolver;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class Connection
 *
 * @package Matchory\Elasticsearch
 */
class Connection implements ConnectionInterface
{
    use ForwardsCalls;

    private const DEFAULT_LOGGER_NAME = 'elasticsearch';

    /**
     * @var Resolver
     * @todo remove in next major version
     */
    private static $resolver;

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
     * @var string|null
     */
    protected $index;

    /**
     * Creates a new connection manager
     *
     * @param Client      $client
     * @param string|null $index
     */
    public function __construct(Client $client, ?string $index = null)
    {
        $this->client = $client;
        $this->index = $index;
    }

    /**
     * Set the connection resolver instance.
     *
     * @param Resolver $resolver
     *
     * @return void
     * @internal
     * @deprecated
     * @todo remove in next major version
     */
    public static function setConnectionResolver(Resolver $resolver): void
    {
        static::$resolver = $resolver;
    }

    /**
     * Create a native connection suitable for any non-laravel or non-lumen apps
     * any composer based frameworks
     *
     * @param $config
     *
     * @return Query
     * @throws BindingResolutionException
     * @deprecated Use the connection manager to create connections instead. It
     *             provides a simpler way to manage connections. This method
     *             will be removed in the next major version.
     * @see        ConnectionManager
     */
    public static function create($config): Query
    {
        $app = App::getFacadeApplication();
        $client = $app
            ->make(ClientFactoryInterface::class)
            ->createClient(
                $config['servers'],
                $config['handler'] ?? null
            );

        return (new static(
            $client,
            $config['index'] ?? null
        ))->newQuery();
    }

    /**
     * @param ClientBuilder $clientBuilder
     * @param array         $config
     *
     * @return ClientBuilder
     * @throws InvalidArgumentException
     * @deprecated Use the connection manager to create connections instead. It
     *             provides a simpler way to manage connections. This method
     *             will be removed in the next major version.
     * @see        ConnectionManager
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
     * @param string $name
     *
     * @return Query
     * @deprecated Use the connection manager to create connections instead. It
     *             provides a simpler way to manage connections. This method
     *             will be removed in the next major version.
     * @see        ConnectionManager
     */
    public function connection(string $name): Query
    {
        return $this->newQuery($name);
    }

    /**
     * route the request to the query class
     *
     * @param string|null $connection Deprecated parameter: Use the proper
     *                                connection instance directly instead of
     *                                passing the name. This parameter will be
     *                                removed in the next major version.
     *
     * @return Query
     */
    public function newQuery(?string $connection = null): Query
    {
        // TODO: This is deprecated behaviour and should be removed in the next
        //       major version.
        if ($connection) {
            return static::$resolver
                ->connection($connection)
                ->newQuery();
        }

        return Query::build($this->client, $this->index);
    }

    /**
     * Check if the connection is already loaded
     *
     * @param string $name
     *
     * @return bool
     * @deprecated Use the connection manager to create connections instead. It
     *             provides a simpler way to manage connections. This method
     *             will be removed in the next major version.
     * @see        ConnectionManager
     */
    public function isLoaded(string $name): bool
    {
        return (bool)static::$resolver->connection($name);
    }

    /**
     * Proxy  calls to the default connection
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments)
    {
        return $this->forwardCallTo(
            $this->newQuery(),
            $name,
            $arguments
        );
    }
}
