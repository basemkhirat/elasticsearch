<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch\Concerns;

use Matchory\Elasticsearch\Index;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;
use RuntimeException;

trait ManagesIndices
{
    /**
     * Retrieves the underlying Elasticsearch connection.
     *
     * @return ConnectionInterface
     */
    abstract public function getConnection(): ConnectionInterface;

    /**
     * Retrieves the name of the current index.
     *
     * @return string|null
     */
    abstract public function getIndex(): ?string;

    /**
     * Create a new index
     *
     * @param string        $name
     * @param callable|null $callback
     *
     * @return array
     */
    public function createIndex(string $name, ?callable $callback = null): array
    {
        $index = new Index($name, $callback);

        $index->setClient($this->getConnection()->getClient());

        return $index->create();
    }

    /**
     * Create the configured index
     *
     * @param callable|null $callback
     *
     * @return array
     * @throws RuntimeException
     * @see Query::createIndex()
     */
    public function create(?callable $callback = null): array
    {
        if ( ! $this->getIndex()) {
            throw new RuntimeException('No index name configured');
        }

        return $this->createIndex($this->getIndex(), $callback);
    }

    /**
     * Check existence of index
     *
     * @return bool
     * @throws RuntimeException
     */
    public function exists(): bool
    {
        if ( ! $this->getIndex()) {
            throw new RuntimeException('No index configured');
        }

        $index = new Index($this->getIndex());

        $index->setClient($this->getConnection()->getClient());

        return $index->exists();
    }

    /**
     * Drop index
     *
     * @param string $name
     *
     * @return array
     */
    public function dropIndex(string $name): array
    {
        $index = new Index($name);

        $index->client = $this->getConnection();

        return $index->drop();
    }

    /**
     * Drop the configured index
     *
     * @return array
     * @throws RuntimeException
     */
    public function drop(): array
    {
        if ( ! $this->getIndex()) {
            throw new RuntimeException('No index name configured');
        }

        return $this->dropIndex($this->getIndex());
    }
}
