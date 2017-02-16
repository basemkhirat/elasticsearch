<?php

namespace Basemkhirat\Elasticsearch;

use Elasticsearch\ClientBuilder;

/**
 * Class Connection
 * @package Basemkhirat\Elasticsearch
 */
class Connection
{

    /**
     * Laravel app instance
     * @var \Illuminate\Foundation\Application|mixed
     */
    protected $app;

    /**
     * Elastic config content
     * @var
     */
    protected $config;

    /**
     * The current connection
     * @var
     */
    protected $connection;

    /**
     * all available connections
     * @var array
     */
    protected $connections = [];


    /**
     * Connection constructor.
     */
    function __construct()
    {
        $this->app = app();

        $this->config = $this->app['config']['es'];
    }

    /**
     * Create a native connection
     * suitable for any non-laravel or non-lumen apps
     * any composer based frameworks
     * @param $config
     * @return Query
     */
    public static function create($config)
    {

        $clientBuilder = ClientBuilder::create();

        $clientBuilder->setHosts($config["servers"]);

        $query = new Query($clientBuilder->build());

        if (array_key_exists("index", $config) and $config["index"] != "") {
            $query->index($config["index"]);
        }

        if (array_key_exists("type", $config) and $config["type"] != "") {
            $query->type($config["type"]);
        }

        return $query;
    }


    /**
     * Create a connection for laravel or lumen frameworks
     * @param $name
     * @return Query
     */
    function connection($name)
    {

        // Check if connection is already loaded.

        if ($this->isLoaded($name)) {

            $this->connection = $this->connections[$name];

            return $this->query($name);

        }

        // Create a new connection.

        if (array_key_exists($name, $this->config["connections"])) {

            // Instantiate a new ClientBuilder
            $clientBuilder = ClientBuilder::create();

            $clientBuilder->setHosts($this->config["connections"][$name]["servers"]);

            // Build the client object
            $connection = $clientBuilder->build();

            $this->connection = $connection;

            $this->connections[$name] = $connection;

            return $this->query($name);
        }

        $this->app->abort(500, "Invalid elasticsearch connection driver `" . $name . "`");

    }


    /**
     * route the request to the query class
     * @param $connection
     * @return Query
     */
    function query($connection)
    {

        $config = $this->config["connections"][$connection];

        $query = new Query($this->connections[$connection]);

        if (array_key_exists("index", $config) and $config["index"] != "") {
            $query->index($config["index"]);
        }

        if (array_key_exists("type", $config) and $config["type"] != "") {
            $query->type($config["type"]);
        }

        return $query;

    }

    /**
     * Check if the connection is already loaded
     * @param $name
     * @return bool
     */
    function isLoaded($name)
    {

        if (array_key_exists($name, $this->connections)) {
            return true;
        }

        return false;

    }


    /**
     * Set the default connection
     * @param $name
     * @param $arguments
     * @return mixed
     */
    function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {

            return call_user_func_array([$this, $name], $arguments);

        } else {

            // if no connection, use default.

            $query = $this->connection($this->config["default"]);

            return call_user_func_array([$query, $name], $arguments);

        }
    }

}
