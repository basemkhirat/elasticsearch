<?php

namespace Matchory\Elasticsearch;

use Elasticsearch\Client;

use function array_merge;
use function array_unique;
use function count;
use function func_get_args;
use function is_array;
use function is_callback_function;

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
     * @var Client
     */
    public $client;

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
     * @var null
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
     * @var int
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
     * Set index shards
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
     * Set index replicas
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
     * Ignore bad HTTP requests
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
     * Check existence of index
     *
     * @return bool
     */
    public function exists(): bool
    {
        $params = [
            'index' => $this->name,
        ];

        return $this->client->indices()->exists($params);
    }

    /**
     * Create a new index
     *
     * @return array
     */
    public function create(): array
    {
        $callback = $this->callback;

        if (is_callback_function($callback)) {
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

        return $this->client
            ->indices()
            ->create($params);
    }

    /**
     * Drop index
     *
     * @return array
     */
    public function drop(): array
    {
        $params = [
            'index' => $this->name,
            'client' => ['ignore' => $this->ignores],
        ];

        return $this->client->indices()->delete($params);
    }

    /**
     * Fields mappings
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
        return $this->client;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }
}


