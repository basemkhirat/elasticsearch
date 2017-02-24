<?php

namespace Basemkhirat\Elasticsearch;


/**
 * Elasticsearch data model
 * Class Model
 * @package Basemkhirat\Elasticsearch
 */
class Model
{

    /**
     * Fill model properties
     * Model constructor.
     * @param array $data
     */
    function __construct($data = [])
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     * Return NULL if property is not exist
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        return (isset($this->$name)) ? $this->$name : null;
    }

}
