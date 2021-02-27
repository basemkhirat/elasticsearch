<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch\Concerns;

use Matchory\Elasticsearch\Index;
use RuntimeException;

trait ManagesIndices
{
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

        $index->setConnection($this->getConnection());

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
        $index = $this->getIndex();

        if ( ! $index) {
            throw new RuntimeException('No index configured');
        }

        return $this->createIndex($index, $callback);
    }

    /**
     * Check existence of index
     *
     * @return bool
     * @throws RuntimeException
     */
    public function exists(): bool
    {
        $index = $this->getIndex();

        if ( ! $index) {
            throw new RuntimeException('No index configured');
        }

        $index = new Index($index);

        $index->setConnection($this->getConnection());

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

        $index->connection = $this->getConnection();

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
        $index = $this->getIndex();

        if ( ! $index) {
            throw new RuntimeException('No index name configured');
        }

        return $this->dropIndex($index);
    }
}
