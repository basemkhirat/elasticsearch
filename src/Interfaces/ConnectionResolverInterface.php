<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch\Interfaces;

interface ConnectionResolverInterface
{
    /**
     * Get a database connection instance.
     *
     * @param string|null $name
     *
     * @return ConnectionInterface
     */
    public function connection(?string $name = null): ConnectionInterface;

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection(): string;

    /**
     * Set the default connection name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setDefaultConnection(string $name): void;
}
