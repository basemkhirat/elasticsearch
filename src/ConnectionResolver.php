<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch;

use Matchory\Elasticsearch\Interfaces\ConnectionInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionResolverInterface;

use function is_null;

/**
 * Connection Resolver
 * ===================
 * Simple resolver intended for ad-hoc implementations and tests
 *
 * @package Matchory\Elasticsearch
 */
class ConnectionResolver implements ConnectionResolverInterface
{
    /**
     * All of the registered connections.
     *
     * @var array<string, ConnectionInterface>
     */
    protected $connections = [];

    /**
     * The default connection name.
     *
     * @var string|null
     */
    protected $default;

    /**
     * Create a new connection resolver instance.
     *
     * @param array<string, ConnectionInterface> $connections
     */
    public function __construct(array $connections = [])
    {
        foreach ($connections as $name => $connection) {
            $this->addConnection($name, $connection);
        }
    }

    /**
     * Get a connection instance by name.
     *
     * @param string|null $name
     *
     * @return ConnectionInterface
     */
    public function connection(?string $name = null): ConnectionInterface
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
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
        return $this->default ?? '';
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
        $this->default = $name;
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
}
