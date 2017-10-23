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
     * Number of pending operations
     * @var int
     */
    public $operationCount = 0;

    /**
     * Operation count which will trigger autocommit
     * @var int
     */
    public $autocommitAfter = 0;


    /**
     * Bulk constructor.
     * @param Query $query
     * @param int $autocommitAfter
     */
    public function __construct(Query $query, $autocommitAfter = 0)
    {

        $this->query = $query;

        $this->autocommitAfter = intval($autocommitAfter);
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
     * Add pending document for insert
     * @param array $data
     * @return mixed
     */
    public function insert($data = [])
    {
        return $this->action('index', $data);
    }

    /**
     * Add pending document for update
     * @param array $data
     * @return mixed
     */
    public function update($data = [])
    {

        return $this->action('update', $data);
    }

    /**
     * Add pending document for deletion
     */
    public function delete()
    {

        return $this->action('delete');
    }

    /**
     * Add pending document abstract action
     * @param string $actionType
     * @param array $data
     * @return mixed
     */
    public function action($actionType, $data = [])
    {

        $this->body["body"][] = [

            $actionType => [
                '_index' => $this->getIndex(),
                '_type' => $this->getType(),
                '_id' => $this->_id
            ]

        ];

        if (!empty($data)) {
            if($actionType == "update"){
                $this->body["body"][] = ["doc" => $data];
            }else {
                $this->body["body"][] = $data;
            }
        }

        $this->operationCount++;

        $this->reset();

        if ($this->autocommitAfter > 0 && $this->operationCount >= $this->autocommitAfter) {
            return $this->commit();
        }

        return true;
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

    /**
     * Commit all pending operations
     */
    public function commit()
    {

        if (empty($this->body)) {
            return false;
        }

        $result = $this->query->connection->bulk($this->body);
        $this->operationCount = 0;
        $this->body = [];

        return $result;
    }
}
