<?php

namespace Basemkhirat\Elasticsearch;

<<<<<<< HEAD
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
=======
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44
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
     * Ignored HTTP errors
     * @var array
     */
    public $ignores = [400, 404, 500];

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
     * Query type key
     * @var
     */
    protected $_id;

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
<<<<<<< HEAD
     * Query scroll time
     * @var string
     */
    protected $scroll;

    /**
     * Query scroll id
     * @var string
     */
    protected $scroll_id;

    /**
     * Query search type
     * @var int
     */
    protected $search_type;

    /**
=======
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44
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
<<<<<<< HEAD

        return $this->type;

    }

    /**
     * Set the query scroll
     * @param string $scroll
     * @return $this
     */
    public function scroll($scroll)
    {

        $this->scroll = $scroll;

        return $this;
    }

    /**
     * Set the query scroll ID
     * @param string $scroll
     * @return $this
     */
    public function scrollID($scroll)
    {

        $this->scroll_id = $scroll;

        return $this;
    }

    /**
     * Set the query search type
     * @param string $type
     * @return $this
     */
    public function searchType($type)
    {

        $this->search_type = $type;

        return $this;
    }

    /**
     * get the query search type
     * @return $this
     */
    public function getSearchType()
    {

        return $this->search_type;

    }

    /**
     * Get the query scroll
     * @return $this
     */
    public function getScroll()
    {

        return $this->scroll;

=======
        return $this->type;
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44
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
<<<<<<< HEAD
     * Ignore bad HTTP response
     * @param array|int $code
     * @return $this
     */
    public function ignore($code)
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
     * Get the query limit
=======
     * get the query limit
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44
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
     * Filter by _id
     * @param bool $_id
     * @return $this
     */
    public function _id($_id = false)
    {

        $this->_id = $_id;

        $this->filter[] = ["term" => ["_id" => $_id]];

        return $this;

    }

    /**
<<<<<<< HEAD
     * Set the query where clause
=======
     * set the query where clause
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44
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

            if ($name == "_id") {
                return $this->_id($value);
            }

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
<<<<<<< HEAD
     * Set the query inverse where clause
=======
     * set the query inverse where clause
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44
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
<<<<<<< HEAD
     * Set the query where between clause
=======
     * set the query where between clause
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44
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
<<<<<<< HEAD
     * Set the query where not between clause
=======
     * set the query where not between clause
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44
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
<<<<<<< HEAD
     * Set the query where in clause
=======
     * set the query where in clause
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44
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
<<<<<<< HEAD
     * Set the query where not in clause
=======
     * set the query where not in clause
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44
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
<<<<<<< HEAD
     * Set the query where exists clause
=======
     * set the query where exists clause
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44
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
     * @param int $boost
     * @return $this
     */
    public function search($q = NULL, $boost = 1)
    {

        if ($q) {

            $this->must[] = [
                "query_string" => [
                    "query" => $q,
                    "boost" => $boost
                ]
            ];

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

            "size" => $this->getTake(),

            'client' => ['ignore' => $this->ignores]

        ];

<<<<<<< HEAD
        $search_type = $this->getSearchType();

        if($search_type){
            $query["search_type"] = $search_type;
        }

        $scroll = $this->getScroll();

        if($scroll){
            $query["scroll"] = $scroll;
        }


=======
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44
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


<<<<<<< HEAD

    /**
     * Clear scroll query id
     * @param  string $scroll_id
     * @return array|Collection
     */
    public function clear($scroll_id = NULL)
    {

        $scroll_id = !is_null($scroll_id) ? $scroll_id : $this->scroll_id;

        return $this->connection->clearScroll([
            "scroll_id" => $scroll_id,
            'client' => ['ignore' => $this->ignores]
        ]);

    }

    /**
     * Get the collection of results
     * @param string $scroll_id
     * @return array|Collection
     */
    public function get($scroll_id = NULL)
    {

        $scroll_id = !is_null($scroll_id) ? $scroll_id : $this->scroll_id;

        if ($scroll_id) {

            $result = $this->connection->scroll([
                "scroll" => $this->scroll,
                "scroll_id" => $scroll_id
            ]);

        } else {

            $query = $this->query();

            $this->validate($query);

            $result = $this->connection->search($query);
        }
=======
    /**
     * Get the collection of results
     * @return array|Collection
     */
    public function get()
    {

        $query = $this->query();

        $this->validate($query);

        $result = $this->connection->search($query);
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44

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
            $original["_score"] = $row["_score"];

            $new[] = (object)$original;

        }

        $new = new Collection($new);

        $new->total = $result["hits"]["total"];
        $new->max_score = $result["hits"]["max_score"];
        $new->took = $result["took"];
        $new->timed_out = $result["timed_out"];
<<<<<<< HEAD
        $new->scroll_id = isset($result["_scroll_id"]) ? $result["_scroll_id"] : NULL;
        $new->shards = (object)$result["_shards"];
=======
        $new->_shards = (object)$result["_shards"];
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44

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
            $original["_score"] = $data[0]["_score"];

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
     * @param null $_id
     * @return object
     */
    public function insert($data, $_id = NULL)
    {

        if ($_id) {
            $this->_id = $_id;
        }

        $parameters = [
            "index" => $this->getIndex(),
            "type" => $this->getType(),
            "id" => $this->_id,
            "body" => $data,
            'client' => ['ignore' => $this->ignores]
        ];

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
     * @param null $_id
     * @return object
     */
    public function update($data, $_id = NULL)
    {

        if ($_id) {
            $this->_id = $_id;
        }

        $parameters = [
            "index" => $this->getIndex(),
            "type" => $this->getType(),
            "id" => $this->_id,
            "body" => ['doc' => $data],
            'client' => ['ignore' => $this->ignores]
        ];

        return (object)$this->connection->update($parameters);

    }


    /**
     * Increment a document field
     * @param $field
     * @param int $count
     * @return object
     */
    public function increment($field, $count = 1)
    {

        return $this->script("ctx._source.$field += params.count", [
            "count" => $count
        ]);

    }

    /**
     * Increment a document field
     * @param $field
     * @param int $count
     * @return object
     */
    public function decrement($field, $count = 1)
    {

        return $this->script("ctx._source.$field -= params.count", [
            "count" => $count
        ]);

    }


    /**
     * Update by script
     * @param $script
     * @param array $params
     * @return object
     */
    public function script($script, $params = [])
    {

        $parameters = [
            "index" => $this->getIndex(),
            "type" => $this->getType(),
            "id" => $this->_id,
            "body" => [
                "script" => [
                    "inline" => $script,
                    "params" => $params
                ]
            ],
            'client' => ['ignore' => $this->ignores]
        ];

        return (object)$this->connection->update($parameters);

    }

    /**
     * Delete a document
     * @param null $_id
     * @return object
     */
    public function delete($_id = NULL)
    {

        if ($_id) {
            $this->_id = $_id;
        }

        $parameters = [
            "index" => $this->getIndex(),
            "type" => $this->getType(),
            "id" => $this->_id,
            'client' => ['ignore' => $this->ignores]
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


    /**
     * Create a new index
     * @param $name
     * @param bool $callback
     * @return mixed
     */
<<<<<<< HEAD
    function createIndex($name, $callback = false)
    {
=======
    function createIndex($name, $callback = false){
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44

        $index = new Index($name, $callback);

        $index->connection = $this->connection;

        return $index->create();

    }


    /**
     * Drop index
     * @param $name
     * @return mixed
     */
<<<<<<< HEAD
    function dropIndex($name)
    {
=======
    function dropIndex($name){
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44

        $index = new Index($name);

        $index->connection = $this->connection;

        return $index->drop();

    }


    /**
     * create a new index [alias to createIndex method]
     * @param bool $callback
     * @return mixed
     */
<<<<<<< HEAD
    function create($callback = false)
    {
=======
    function create($callback = false){
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44

        $index = new Index($this->index, $callback);

        $index->connection = $this->connection;

        return $index->create();

    }

    /**
     * Drop index [alias to dropIndex method]
     * @return mixed
     */
<<<<<<< HEAD
    function drop()
    {
=======
    function drop(){
>>>>>>> 6fdc2f25787d06ee7bcbcfcd82f36e727c753e44

        $index = new Index($this->index);

        $index->connection = $this->connection;

        return $index->drop();

    }

}