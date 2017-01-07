<?php

namespace Basemkhirat\Elasticsearch;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

/**
 * Class Query
 * @package Basemkhirat\Elasticsearch\Query
 */
class Query
{

    /**
     * Native elasticsearch connection instance
     * @var Connection
     */
    public $connection;

    /**
     * Filter operators
     * @var array
     */
    protected $operators = [
        "=",
        "!=",
        ">",
        ">=",
        "<",
        "<=",
        "like",
        "exists"
    ];

    /**
     * Query array
     * @var
     */
    protected $query;

    /**
     * Query index name
     * @var
     */
    protected $index;

    /**
     * Query type name
     * @var
     */
    protected $type;

    /**
     * Query body
     * @var array
     */
    protected $body = [];

    /**
     * Query bool filter
     * @var array
     */
    protected $filter = [];

    /**
     * Query bool must
     * @var array
     */
    protected $must = [];

    /**
     * Query bool must not
     * @var array
     */
    protected $must_not = [];

    /**
     * Query returned fields list
     * @var array
     */
    protected $_source = [];

    /**
     * Query sort fields
     * @var array
     */
    protected $sort = [];

    /**
     * Query limit
     * @var int
     */
    protected $take = 10;

    /**
     * Query offset
     * @var int
     */
    protected $skip = 0;

    /**
     * Query constructor.
     * @param $connection
     */
    function __construct($connection)
    {
        $this->app = app();
        $this->connection = $connection;
    }

    /**
     * Set the index name
     * @param $index
     * @return $this
     */
    public function index($index)
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
        return $this->index;
    }

    /**
     * Set the type name
     * @param $type
     * @return $this
     */
    public function type($type)
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
        return $this->type;
    }

    /**
     * Set the query limit
     * @param int $take
     * @return $this
     */
    public function take($take = 10)
    {

        $this->take = $take;

        return $this;
    }

    /**
     * get the query limit
     * @return int
     */
    protected function getTake()
    {
        return $this->take;
    }

    /**
     * Set the query offset
     * @param int $skip
     * @return $this
     */
    public function skip($skip = 0)
    {

        $this->skip = $skip;

        return $this;
    }

    /**
     * Get the query offset
     * @return int
     */
    protected function getSkip()
    {
        return $this->skip;
    }


    /**
     * Generate the query body
     * @return array
     */
    protected function getBody()
    {

        if (count($this->must) || count($this->must_not) || count($this->filter) || count($this->sort) || count($this->_source)) {

            $this->body = [

                "_source" => $this->_source,

                "query" => [

                    "bool" => [

                        "must" => $this->must,

                        "must_not" => $this->must_not,

                        "filter" => $this->filter

                    ]
                ],

                "sort" => $this->sort,

            ];

        }

        return $this->body;

    }


    /**
     * Set the sorting field
     * @param $field
     * @param string $direction
     * @return $this
     */
    public function orderBy($field, $direction = "asc")
    {

        $this->sort[] = [$field => $direction];

        return $this;
    }

    /**
     * check if it's a valid operator
     * @param $string
     * @return bool
     */
    protected function isOperator($string)
    {

        if (in_array($string, $this->operators)) {
            return true;
        }

        return false;

    }

    /**
     * Set the query fields to return
     * @param $field
     * @return $this
     */
    public function select($field)
    {

        $args = func_get_args();

        foreach ($args as $arg) {

            if (is_array($arg)) {
                $this->_source = array_merge($this->_source, $arg);
            } else {
                $this->_source[] = $arg;
            }

        }

        return $this;

    }

    /**
     * set the query where clause
     * @param $name
     * @param string $operator
     * @param null $value
     * @return $this
     */
    public function where($name, $operator = "=", $value = NULL)
    {

        if (is_callable($name)) {
            $name($this);
            return $this;
        }

        if (!$this->isOperator($operator)) {
            $value = $operator;
            $operator = "=";
        }

        if ($operator == "=") {
            $this->filter[] = ["term" => [$name => $value]];
        }

        if ($operator == ">") {
            $this->filter[] = ["range" => [$name => ["gt" => $value]]];
        }

        if ($operator == ">=") {
            $this->filter[] = ["range" => [$name => ["gte" => $value]]];
        }

        if ($operator == "<") {
            $this->filter[] = ["range" => [$name => ["lt" => $value]]];
        }

        if ($operator == "<=") {
            $this->filter[] = ["range" => [$name => ["lte" => $value]]];
        }

        if ($operator == "like") {
            $this->must[] = ["match" => [$name => $value]];
        }

        if ($operator == "exists") {
            $this->whereExists($name, $value);
        }

        return $this;

    }

    /**
     * set the query inverse where clause
     * @param $name
     * @param string $operator
     * @param null $value
     * @return $this
     */
    public function whereNot($name, $operator = "=", $value = NULL)
    {

        if (is_callable($name)) {
            $name($this);
            return $this;
        }

        if (!$this->isOperator($operator)) {
            $value = $operator;
            $operator = "=";
        }

        if ($operator == "=") {
            $this->must_not[] = ["term" => [$name => $value]];
        }

        if ($operator == ">") {
            $this->must_not[] = ["range" => [$name => ["gt" => $value]]];
        }

        if ($operator == ">=") {
            $this->must_not[] = ["range" => [$name => ["gte" => $value]]];
        }

        if ($operator == "<") {
            $this->must_not[] = ["range" => [$name => ["lt" => $value]]];
        }

        if ($operator == "<=") {
            $this->must_not[] = ["range" => [$name => ["lte" => $value]]];
        }

        if ($operator == "like") {
            $this->must_not[] = ["match" => [$name => $value]];
        }

        if ($operator == "exists") {
            $this->whereExists($name, !$value);
        }

        return $this;

    }

    /**
     * set the query where between clause
     * @param $name
     * @param $first_value
     * @param $last_value
     * @return $this
     */
    public function whereBetween($name, $first_value, $last_value)
    {

        $this->filter[] = ["range" => [$name => ["gte" => $first_value, "lte" => $last_value]]];

        return $this;

    }

    /**
     * set the query where not between clause
     * @param $name
     * @param $first_value
     * @param $last_value
     * @return $this
     */
    public function whereNotBetween($name, $first_value, $last_value)
    {

        $this->must_not[] = ["range" => [$name => ["gte" => $first_value, "lte" => $last_value]]];

        return $this;

    }

    /**
     * set the query where in clause
     * @param $name
     * @param array $value
     * @return $this
     */
    public function whereIn($name, $value = [])
    {

        if (is_callable($name)) {
            $name($this);
            return $this;
        }

        $this->filter[] = ["terms" => [$name => $value]];

        return $this;

    }

    /**
     * set the query where not in clause
     * @param $name
     * @param array $value
     * @return $this
     */
    public function whereNotIn($name, $value = [])
    {

        if (is_callable($name)) {
            $name($this);
            return $this;
        }

        $this->must_not[] = ["terms" => [$name => $value]];

        return $this;

    }


    /**
     * set the query where exists clause
     * @param $name
     * @param bool $exists
     * @return $this
     */
    public function whereExists($name, $exists = true)
    {

        if ($exists) {
            $this->must[] = ["exists" => ["field" => $name]];
        } else {
            $this->must_not[] = ["exists" => ["field" => $name]];
        }

        return $this;

    }

    /**
     * Search the entire document fields
     * @param null $q
     * @return $this
     */
    public function search($q = NULL)
    {

        if ($q) {
            $this->must[] = ["query_string" => ["query" => $q]];
        }

        return $this;

    }

    /**
     * Generate the query to be executed
     * @return array
     */
    public function query()
    {

        $query = [

            'index' => $this->getIndex(),

            'type' => $this->getType(),

            'body' => $this->getBody(),

            "from" => $this->getSkip(),

            "size" => $this->getTake()

        ];

        return $query;

    }


    /**
     * Validate index and type names
     * @param $query
     */
    protected function validate($query)
    {
        if (!$this->index) {
            return $this->app->abort(500, "Index missing " . json_encode($query));
        }

        if (!$this->type) {
            return $this->app->abort(500, "Type missing " . json_encode($query));
        }
    }


    /**
     * Get the collection of results
     * @return array|Collection
     */
    public function get()
    {

        $query = $this->query();

        $this->validate($query);

        $result = $this->connection->search($query);

        return $this->getAll($result);

    }

    /**
     * Get the count of results
     * @return mixed
     */
    public function count()
    {
        return $this->get()->total;
    }


    /**
     * Get the first object of results
     * @return object
     */
    public function first()
    {

        $this->take(1);

        $query = $this->query();

        $this->validate($query);

        $result = $this->connection->search($query);

        return $this->getFirst($result);

    }

    /**
     * @param array $result
     * @return array|Collection
     */
    protected function getAll($result = [])
    {

        $new = [];

        foreach ($result["hits"]["hits"] as $row) {

            $original = $row["_source"];

            $original["_id"] = $row["_id"];

            $new[] = (object)$original;

        }

        $new = new Collection($new);

        $new->total = $result["hits"]["total"];
        $new->max_score = $result["hits"]["max_score"];
        $new->took = $result["took"];
        $new->timed_out = $result["timed_out"];
        $new->_shards = (object)$result["_shards"];

        return $new;
    }

    /**
     * @param array $result
     * @return object
     */
    protected function getFirst($result = [])
    {

        $data = $result["hits"]["hits"];

        $new = (object)[];

        if (count($data)) {

            $original = $data[0]["_source"];

            $original["_id"] = $data[0]["_id"];

            $new = (object)$original;

        }

        return $new;
    }

    /**
     * Paginate the collection of results
     * @param int $per_page
     * @return LengthAwarePaginator
     */
    public function paginate($per_page = 10)
    {

        $this->take($per_page);

        $page = (int)Request::get('page', 1);

        $this->skip(($page * $per_page) - $per_page);

        $objects = $this->get();

        return new LengthAwarePaginator($objects, $objects->total, $per_page, Request::get("page"), ['path' => Request::url(), 'query' => Request::query()]);

    }

    /**
     * Insert a document
     * @param $data
     * @param null $id
     * @return object
     */
    public function insert($data, $id = NULL)
    {

        $parameters = [
            "index" => $this->getIndex(),
            "type" => $this->getType(),
            "body" => $data
        ];

        if ($id) {
            $parameters["id"] = $id;
        }

        return (object)$this->connection->index($parameters);

    }

    /**
     * Insert a bulk of documents
     * @param $data multidimensional array of [id => data] pairs
     * @return object
     */
    public function bulk($data)
    {

        $params = [];

        foreach ($data as $key => $value) {

            $params["body"][] = [

                'index' => [
                    '_index' => $this->getIndex(),
                    '_type' => $this->getType(),
                    '_id' => $key
                ]

            ];

            $params["body"][] = $value;

        }

        return (object)$this->connection->bulk($params);

    }

    /**
     * Update a document
     * @param $data
     * @param null $id
     * @return object
     */
    public function update($data, $id = NULL)
    {

        $parameters = [
            "index" => $this->getIndex(),
            "type" => $this->getType(),
            "body" => ['doc' => $data]
        ];

        if ($id) {
            $parameters["id"] = $id;
        }

        return (object)$this->connection->update($parameters);

    }

    /**
     * Delete a document
     * @param null $id
     * @return object
     */
    public function delete($id = NULL)
    {

        $parameters = [
            "index" => $this->getIndex(),
            "type" => $this->getType(),
            "id" => $id,
            'client' => ['ignore' => [400, 404]]
        ];

        return (object)$this->connection->delete($parameters);

    }


    /**
     * Return the native connection to execute native query
     * @return object
     */
    public function raw()
    {
        return $this->connection;
    }




}