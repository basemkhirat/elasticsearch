<?php

namespace Basemkhirat\Elasticsearch;

/**
 * Class Request
 * @package Basemkhirat\Elasticsearch
 */
class Request
{

    /**
     * Get the request url
     * @return string
     */
    public static function url()
    {

        $server = $_SERVER;

        $ssl = (!empty($server['HTTPS']) && $server['HTTPS'] == 'on');

        $sp = strtolower($server['SERVER_PROTOCOL']);

        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');

        $port = $server['SERVER_PORT'];

        $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;

        $host = isset($host) ? $host : $server['SERVER_NAME'] . $port;

        $host .= preg_replace("/\?.*/", "", $server["REQUEST_URI"]);

        return $protocol . '://' . $host;
    }

    /**
     * Get all query string parameters
     * @return mixed
     */
    public static function query()
    {
        return $_GET;
    }

    /**
     * Get value of query string parameter
     * @param $name
     * @param null $value
     * @return null
     */
    public static function get($name, $value = NULL)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $value;
    }

}
