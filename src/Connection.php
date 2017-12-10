<?php

namespace Basemkhirat\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Symfony\Component\HttpKernel\Exception\HttpException as HttpException;

/**
 * Class Connection
 * @package Basemkhirat\Elasticsearch
 */
class Connection
{

    /**
     * Laravel app instance
     * @var \Illuminate\contracts\Foundation\Application|mixed
     */
    protected $app;

    /**
     * Elastic config content
     * @var
     */
    protected $config;

    /**
     * The current connection
     * @var Client
     */
    protected $connection;

    /**
     * all available connections
     * @var Client[]
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

        if (!empty($config['handler'])) {
            $clientBuilder->setHandler($config['handler']);
        }

        $clientBuilder->setHosts($config["servers"]);

        $query = new Query($clientBuilder->build());

        if (array_key_exists("index", $config) and $config["index"] != "") {
            $query->index($config["index"]);
        }

        return $query;
    }


    /**
     * Create a connection for laravel or lumen frameworks
     * @param $name
     * @throws HttpException
     * @return Query
     */
    function connection($name)
    {
        // Check if connection is already loaded.

        if ($this->isLoaded($name)) {

            $this->connection = $this->connections[$name];

            return $this->newQuery($name);

        }

        // Create a new connection.

        if (array_key_exists($name, $this->config["connections"])) {

            $config = $this->config["connections"][$name];

            // Instantiate a new ClientBuilder
            $clientBuilder = ClientBuilder::create();

            $clientBuilder->setHosts($config["servers"]);

            if (!empty($config['handler'])) {
                $clientBuilder->setHandler($config['handler']);
            }

            // Build the client object
            $connection = $clientBuilder->build();

            $this->connection = $connection;

            $this->connections[$name] = $connection;

            return $this->newQuery($name);
        }

        $this->app->abort(500, "Invalid elasticsearch connection driver `" . $name . "`");
    }


    /**
     * @param string $name
     * @throws HttpException
     * @return Query
     */
    function connectionByName($name = 'default') {
        return $this->connection($this->config[$name]);
    }


    /**
     * route the request to the query class
     * @param $connection
     * @return Query
     */
    function newQuery($connection)
    {

        $config = $this->config["connections"][$connection];

        $query = new Query($this->connections[$connection]);

        if (array_key_exists("index", $config) and $config["index"] != "") {
            $query->index($config["index"]);
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

        return array_key_exists($name, $this->connections);
    }


    /**
     * Set the default connection
     * @param $name
     * @param $arguments
     * @throws HttpException
     * @return mixed
     */
    function __call($name, $arguments)
    {

        $query = $this->connectionByName('default');

        return call_user_func_array([$query, $name], $arguments);
    }
}
