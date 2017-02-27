<?php

namespace Basemkhirat\Elasticsearch\Classes;

use Basemkhirat\Elasticsearch\Query;

/**
 * Class Bulk
 * @package Basemkhirat\Elasticsearch\Classes
 */
class Bulk
{

    /**
     * The query object
     * @var Query
     */
    public $query;

    /**
     * The document key
     * @var string
     */
    public $_id;

    /**
     * The index name
     * @var string
     */
    public $index;

    /**
     * The type name
     * @var string
     */
    public $type;

    /**
     * Bulk body
     * @var array
     */
    public $body = [];


    /**
     * Bulk constructor.
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Set the index name
     * @param $index
     * @return $this
     */
    public function index($index = false)
    {

        $this->index = $index;

        return $this;
    }

    /**
     * Get the index name
     * @return mixed
     */
    protected function getIndex()
    {

        return $this->index ? $this->index : $this->query->getIndex();

    }

    /**
     * Set the type name
     * @param $type
     * @return $this
     */
    public function type($type = false)
    {

        $this->type = $type;

        return $this;
    }

    /**
     * Get the type name
     * @return mixed
     */
    protected function getType()
    {

        return $this->type ? $this->type : $this->query->getType();

    }

    /**
     * Filter by _id
     * @param bool $_id
     * @return $this
     */
    public function _id($_id = false)
    {

        $this->_id = $_id;

        return $this;
    }

    /**
     * Just an alias for _id() method
     * @param bool $_id
     * @return $this
     */
    public function id($_id = false)
    {

        return $this->_id($_id);

    }

    /**
     * Add pending document
     * @param array $data
     */
    public function insert($data = [])
    {

        $this->body["body"][] = [

            'index' => [
                '_index' => $this->getIndex(),
                '_type' => $this->getType(),
                '_id' => $this->_id
            ]

        ];

        $this->body["body"][] = $data;

        $this->reset();

    }

    /**
     * Get Bulk body
     * @return array
     */
    public function body()
    {

        return $this->body;

    }

    /**
     * Reset names
     * @return void
     */
    public function reset()
    {

        $this->index(NULL);
        $this->type(NULL);

    }


}
