<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch;

use InvalidArgumentException;
use Matchory\Elasticsearch\Interfaces\ClientFactoryInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionResolverInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

use function is_null;

/**
 * Connection Manager
 * ==================
 * Resolver intended to manage connections to one or more Elasticsearch servers
 * at runtime. It creates connections lazily as requested by the application.
 *
 * @package Matchory\Elasticsearch
 */
class ConnectionManager implements ConnectionResolverInterface
{
    public const CONFIG_KEY_CONNECTIONS = 'connections';

    public const CONFIG_KEY_DEFAULT_CONNECTION = 'default';

    public const CONFIG_KEY_HANDLER = 'handler';

    public const CONFIG_KEY_INDEX = 'index';

    public const CONFIG_KEY_SERVERS = 'servers';

    /**
     * All of the registered connections.
     *
     * @var array<string, ConnectionInterface>
     */
    protected $connections = [];

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var ClientFactoryInterface
     */
    protected $clientFactory;

    /**
     * @var CacheInterface|null
     */
    protected $cache;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * Create a new connection resolver instance.
     *
     * @param array                  $configuration
     * @param ClientFactoryInterface $clientFactory
     * @param CacheInterface|null    $cache
     * @param LoggerInterface|null   $logger
     */
    public function __construct(
        array $configuration,
        ClientFactoryInterface $clientFactory,
        ?CacheInterface $cache = null,
        ?LoggerInterface $logger = null
    ) {
        $this->configuration = $configuration;
        $this->clientFactory = $clientFactory;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Get a connection instance by name.
     *
     * @param string|null $name
     *
     * @return ConnectionInterface
     * @throws InvalidArgumentException
     */
    public function connection(?string $name = null): ConnectionInterface
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        if ( ! isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection($name);
        }

        return $this->connections[$name];
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection(): string
    {
        return $this->configuration[self::CONFIG_KEY_DEFAULT_CONNECTION] ?? '';
    }

    /**
     * Set the default connection name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setDefaultConnection(string $name): void
    {
        $this->configuration[self::CONFIG_KEY_DEFAULT_CONNECTION] = $name;
    }

    /**
     * Add a connection to the resolver.
     *
     * @param string              $name
     * @param ConnectionInterface $connection
     *
     * @return void
     */
    public function addConnection(
        string $name,
        ConnectionInterface $connection
    ): void {
        $this->connections[$name] = $connection;
    }

    /**
     * Check if a connection has been registered.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasConnection(string $name): bool
    {
        return isset($this->connections[$name]);
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function __call(string $method, array $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }

    /**
     * @param string $name
     *
     * @return ConnectionInterface
     * @throws InvalidArgumentException
     */
    protected function makeConnection(string $name): ConnectionInterface
    {
        $config = $this->configuration[self::CONFIG_KEY_CONNECTIONS][$name]
                  ?? null;

        if ( ! $config) {
            throw new InvalidArgumentException(
                "Elasticsearch connection [{$name}] not configured."
            );
        }

        $client = $this->clientFactory->createClient(
            $config[self::CONFIG_KEY_SERVERS],
            $this->logger,
            $config[self::CONFIG_KEY_HANDLER] ?? null
        );

        return new Connection(
            $client,
            $this->cache,
            $config[self::CONFIG_KEY_INDEX] ?? null
        );
    }
}
