<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch;

use InvalidArgumentException;
use Matchory\Elasticsearch\Interfaces\ClientFactoryInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionResolverInterface;

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
     * Create a new connection resolver instance.
     *
     * @param array                  $configuration
     * @param ClientFactoryInterface $clientFactory
     */
    public function __construct(
        array $configuration,
        ClientFactoryInterface $clientFactory
    ) {
        $this->configuration = $configuration;
        $this->clientFactory = $clientFactory;
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
        return $this->configuration['default'];
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
        $this->configuration['default'] = $name;
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
        $config = $this->configuration['connections'][$name] ?? null;

        if ( ! $config) {
            throw new InvalidArgumentException(
                "Elasticsearch connection [{$name}] not configured."
            );
        }

        $client = $this->clientFactory->createClient(
            $config['servers'],
            null,
            $config['handler'] ?? null
        );

        return new Connection($client, $config['index'] ?? null);
    }
}
