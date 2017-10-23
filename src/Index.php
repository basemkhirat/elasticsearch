<?php

namespace Basemkhirat\Elasticsearch;

/**
 * Class Index
 * @package Basemkhirat\Elasticsearch\Query
 */
class Index
{

    /**
     * Native elasticsearch connection instance
     * @var Connection
     */
    public $connection;

    /**
     * Ignored HTTP errors
     * @var array
     */
    public $ignores = [];

    /**
     * Index name
     * @var string
     */
    public $name;


    /**
     * Index create callback
     * @var null
     */
    public $callback;


    /**
     * Index shards
     * @var int
     */
    public $shards = 5;


    /**
     * Index replicas
     * @var int
     */
    public $replicas = 0;

    /**
     * Index mapping
     * @var int
     */
    public $mappings = [];


    /**
     * Index constructor.
     * @param $name
     * @param null $callback
     */
    function __construct($name, $callback = NULL)
    {
        $this->name = $name;
        $this->callback = $callback;
    }

    /**
     * Set index shards
     * @param $shards
     * @return $this
     */
    public function shards($shards)
    {
        $this->shards = $shards;

        return $this;
    }

    /**
     * Set index replicas
     * @param $replicas
     * @return $this
     */
    public function replicas($replicas)
    {

        $this->replicas = $replicas;

        return $this;
    }

    /**
     * Ignore bad HTTP requests
     * @return $this
     */
    public function ignore()
    {

        $args = func_get_args();

        foreach ($args as $arg) {

            if (is_array($arg)) {
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
     * @return mixed
     */
    public function exists()
    {

        $params = [
            'index' => $this->name,
        ];

        return $this->connection->indices()->exists($params);
    }

    /**
     * Create a new index
     * @return mixed
     */
    public function create()
    {

        $callback = $this->callback;

        if (is_callback_function($callback)) {
            $callback($this);
        }

        $params = [

            'index' => $this->name,

            'body' => [
                "settings" => [
                    'number_of_shards' => $this->shards,
                    'number_of_replicas' => $this->replicas
                ]
            ],

            'client' => [
                'ignore' => $this->ignores
            ]
        ];

        if (count($this->mappings)) {
            $params["body"]["mappings"] = $this->mappings;
        }

        return $this->connection->indices()->create($params);
    }

    /**
     * Drop index
     * @return mixed
     */
    public function drop()
    {

        $params = [
            'index' => $this->name,
            'client' => ['ignore' => $this->ignores]
        ];

        return $this->connection->indices()->delete($params);
    }

    /**
     * Fields mappings
     * @param array $mappings
     * @return $this
     */
    public function mapping($mappings = [])
    {

        $this->mappings = $mappings;

        return $this;
    }
}


