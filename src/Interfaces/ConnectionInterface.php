<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch\Interfaces;

use Elasticsearch\Client;
use Matchory\Elasticsearch\Query;
use Psr\SimpleCache\CacheInterface;

/**
 * Interface ConnectionInterface
 *
 * @package Matchory\Elasticsearch\Interfaces
 */
interface ConnectionInterface
{
    /**
     * Creates a new Elasticsearch query
     *
     * @return Query
     */
    public function newQuery(): Query;

    /**
     * Adds a document to the index using the specified parameters.
     *
     * @param array       $parameters Parameters to index the document with
     * @param string|null $index      Index to insert the document into.
     *                                Defaults to the default index of the
     *                                connection.
     * @param string|null $type       Document type to create. Defaults to the
     *                                type of the connection. Usage of this
     *                                parameter is deprecated.
     *
     * @return object
     */
    public function insert(
        array $parameters,
        ?string $index = null,
        ?string $type = null
    ): object;

    /**
     * Create a new query on the given index.
     *
     * @param string $index Name of the index to query.
     *
     * @return Query Query builder instance.
     */
    public function index(string $index): Query;

    /**
     * Retrieves the Elasticsearch client.
     *
     * @return Client
     */
    public function getClient(): Client;

    /**
     * Retrieves the cache instance, if configured.
     *
     * @return CacheInterface|null
     */
    public function getCache(): ?CacheInterface;
}
