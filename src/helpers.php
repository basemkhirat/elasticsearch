<?php

declare(strict_types=1);

if ( ! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param string $path
     *
     * @return string
     */
    function config_path(string $path = ''): string
    {
        return \app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

if ( ! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param string $path
     *
     * @return string
     */
    function base_path(string $path = ''): string
    {
        return \app()->basePath() . ($path ? '/' . $path : $path);
    }
}



