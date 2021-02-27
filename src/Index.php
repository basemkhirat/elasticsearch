<?php

namespace Matchory\Elasticsearch;

use Elasticsearch\Client;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;

use function array_merge;
use function array_unique;
use function count;
use function func_get_args;
use function is_array;

/**
 * Class Index
 *
 * @package Matchory\Elasticsearch\Query
 */
class Index
{
    /**
     * Native elasticsearch client instance
     *
     * @var ConnectionInterface
     */
    public $connection;

    /**
     * Ignored HTTP errors
     *
     * @var array
     */
    public $ignores = [];

    /**
     * Index name
     *
     * @var string
     */
    public $name;

    /**
     * Index create callback
     *
     * @var callable|null
     */
    public $callback;

    /**
     * Index shards
     *
     * @var int
     */
    public $shards = 5;

    /**
     * Index replicas
     *
     * @var int
     */
    public $replicas = 0;

    /**
     * Index mapping
     *
     * @var array
     */
    public $mappings = [];

    /**
     * Index constructor.
     *
     * @param string        $name
     * @param callable|null $callback
     */
    public function __construct(string $name, ?callable $callback = null)
    {
        $this->name = $name;
        $this->callback = $callback;
    }

    /**
     * Configures the index shards.
     *
     * @param int $shards
     *
     * @return $this
     */
    public function shards(int $shards): self
    {
        $this->shards = $shards;

        return $this;
    }

    /**
     * Configures the index replicas.
     *
     * @param int $replicas
     *
     * @return $this
     */
    public function replicas(int $replicas): self
    {
        $this->replicas = $replicas;

        return $this;
    }

    /**
     * Configures the client to ignore bad HTTP requests.
     *
     * @return $this
     */
    public function ignore(): self
    {
        $args = func_get_args();

        foreach ($args as $arg) {
            if (is_array($arg)) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $this->ignores = array_merge($this->ignores, $arg);
            } else {
                $this->ignores[] = $arg;
            }
        }

        $this->ignores = array_unique($this->ignores);

        return $this;
    }

    /**
     * Checks whether an index exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        $params = [
            'index' => $this->name,
        ];

        return $this
            ->connection
            ->getClient()
            ->indices()
            ->exists($params);
    }

    /**
     * Creates a new index
     *
     * @return array
     */
    public function create(): array
    {
        $callback = $this->callback;

        // TODO: Why would you call a callback before actually creating
        //       the index? This should probably be removed
        if ($callback) {
            $callback($this);
        }

        $params = [
            'index' => $this->name,
            'body' => [
                'settings' => [
                    'number_of_shards' => $this->shards,
                    'number_of_replicas' => $this->replicas,
                ],
            ],
            'client' => [
                'ignore' => $this->ignores,
            ],
        ];

        if (count($this->mappings)) {
            $params['body']['mappings'] = $this->mappings;
        }

        return $this
            ->connection
            ->getClient()
            ->indices()
            ->create($params);
    }

    /**
     * Drops an index.
     *
     * @return array
     */
    public function drop(): array
    {
        $params = [
            'index' => $this->name,
            'client' => ['ignore' => $this->ignores],
        ];

        return $this
            ->connection
            ->getClient()
            ->indices()
            ->delete($params);
    }

    /**
     * Sets the fields mappings.
     *
     * @param array $mappings
     *
     * @return $this
     */
    public function mapping(array $mappings = []): self
    {
        $this->mappings = $mappings;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->connection->getClient();
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function setConnection(ConnectionInterface $connection): void
    {
        $this->connection = $connection;
    }
}


