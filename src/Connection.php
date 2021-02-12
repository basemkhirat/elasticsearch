<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch;

use BadMethodCallException;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Traits\ForwardsCalls;
use InvalidArgumentException;
use Matchory\Elasticsearch\Interfaces\ClientFactoryInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionResolverInterface as Resolver;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\SimpleCache\CacheInterface;

/**
 * Connection
 * ==========
 *
 * @package Matchory\Elasticsearch
 */
class Connection implements ConnectionInterface
{
    use ForwardsCalls;

    private const DEFAULT_LOGGER_NAME = 'elasticsearch';

    /**
     * @var Resolver
     * @deprecated
     * @todo remove in next major version
     */
    private static $resolver;

    /**
     * Used to hold all connections.
     *
     * @var Client[]
     * @deprecated
     * @todo remove in next major version
     */
    protected $clients = [];

    /**
     * Elasticsearch client instance used for this connection.
     *
     * @var Client
     * @see Connection::getClient()
     */
    protected $client;

    /**
     * Cache instance to be used for this connection. In Laravel applications,
     * this will be an instance of the Cache Repository, which is the same as
     * the instance returned from the Cache facade.
     *
     * @var CacheInterface|null
     * @see Repository
     * @see Cache
     */
    protected $cache;

    /**
     * @var string|null
     */
    protected $index;

    /**
     * Creates a new connection
     *
     * @param Client              $client
     * @param CacheInterface|null $cache
     * @param string|null         $index
     */
    public function __construct(
        Client $client,
        ?CacheInterface $cache = null,
        ?string $index = null
    ) {
        $this->client = $client;
        $this->index = $index;
        $this->cache = $cache;
    }

    /**
     * Set the connection resolver instance.
     *
     * @param Resolver $resolver
     *
     * @return void
     * @internal
     * @deprecated
     * @todo         remove in next major version
     * @noinspection PhpDeprecationInspection
     */
    public static function setConnectionResolver(Resolver $resolver): void
    {
        static::$resolver = $resolver;
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
     * Check if the connection is already loaded
     *
     * @param string $name
     *
     * @return bool
     * @deprecated   Use the connection manager to create connections instead. It
     *             provides a simpler way to manage connections. This method
     *             will be removed in the next major version.
     * @see          ConnectionManager
     * @noinspection PhpDeprecationInspection
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

    /**
     * @inheritDoc
     */
    public function getCache(): ?CacheInterface
    {
        return $this->cache;
    }

    /**
     * @inheritDoc
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @inheritDoc
     */
    public function index(string $index): Query
    {
        return $this->newQuery()->index($index);
    }

    public function insert(
        array $parameters,
        ?string $index = null,
        ?string $type = null
    ): object {
        if (
            ! isset($parameters[Query::PARAM_INDEX]) &&
            $index = $index ?? $this->index
        ) {
            $parameters[Query::PARAM_INDEX] = $index;
        }

        if ($type) {
            $parameters[Query::PARAM_TYPE] = $type;
        }

        return (object)$this->client->index($parameters);
    }

    /**
     * Route the request to the query class
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
            /** @noinspection PhpDeprecationInspection */
            return static::$resolver
                ->connection($connection)
                ->newQuery();
        }

        $query = new Query($this);

        return $query->index($this->index);
    }
}
