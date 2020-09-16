<?php

namespace Matchory\Elasticsearch;

/**
 * Class Request
 *
 * TODO: Scrap this.
 *
 * @package Matchory\Elasticsearch
 */
class Request
{
    /**
     * Get the request url
     *
     * @return string
     */
    public static function url(): string
    {
        $server = $_SERVER;
        $ssl = ( ! empty($server['HTTPS']) && $server['HTTPS'] === 'on');
        $sp = strtolower($server['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = (int)$server['SERVER_PORT'];
        $port = (( ! $ssl && $port === 80) || ($ssl && $port === 443))
            ? ''
            : ':' . $port;
        $host = $host ?? ($server['SERVER_NAME'] . $port);
        $host .= preg_replace("/\?.*/", "", $server["REQUEST_URI"]);

        return "{$protocol}://{$host}";
    }

    /**
     * Get all query string parameters
     *
     * @return mixed
     */
    public static function query()
    {
        return $_GET;
    }

    /**
     * Get value of query string parameter
     *
     * @param string $name
     * @param mixed  $fallback
     *
     * @return mixed
     */
    public static function get(string $name, $fallback = null)
    {
        return $_GET[$name] ?? $fallback;
    }

}
