<?php

namespace Basemkhirat\Elasticsearch;

use Basemkhirat\Elasticsearch\Classes\Bulk;
use Basemkhirat\Elasticsearch\Classes\Search;


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
    public $ignores = [];

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
    public $body = [];

    /**
     * Query bool filter
     * @var array
     */
    protected $filter = [];

    /**
     * Query bool must
     * @var array
     */
    public $must = [];

    /**
     * Query bool must not
     * @var array
     */
    public $must_not = [];

    /**
     * Query returned fields list
     * @var array
     */
    protected $_source = [
        "include" => [],
        "exclude" => []
    ];

    /**
     * Query sort fields
     * @var array
     */
    protected $sort = [];

    /**
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
     * The key that should be used when caching the query.
     * @var string
     */
    protected $cacheKey;

    /**
     * The number of minutes to cache the query.
     * @var int
     */
    protected $cacheMinutes;

    /**
     * The cache driver to be used.
     * @var string
     */
    protected $cacheDriver;

    /**
     * A cache prefix.
     * @var string
     */
    protected $cachePrefix = 'es';

    /**
     * Elastic model instance
     * @var \Basemkhirat\Elasticsearch\Model
     */
    public $model;

    /**
     * Use model global scopes
     * @var bool
     */
    public $useGlobalScopes = true;


    /**
     * Query constructor.
     * @param $connection
     */
    function __construct($connection = NULL)
    {
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
    public function getIndex()
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
    public function getType()
    {
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
     * Ignore bad HTTP response
     * @return $this
     */
    public function ignore()
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
     * Set the sorting field
     * @param        $field
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
     * @return $this
     */
    public function select()
    {

        $args = func_get_args();

        $fields = [];

        foreach ($args as $arg) {
            if (is_array($arg)) {
                $fields = array_merge($fields, $arg);
            } else {
                $fields[] = $arg;
            }
        }

        $this->_source["include"] = array_unique(array_merge($this->_source["include"], $fields));
        $this->_source["exclude"] = array_values(array_filter($this->_source["exclude"], function ($field) {
            return !in_array($field, $this->_source["include"]);
        }));

        return $this;
    }

    /**
     * Set the ignored fields to not be returned
     * @return $this
     */
    public function unselect()
    {

        $args = func_get_args();

        $fields = [];

        foreach ($args as $arg) {
            if (is_array($arg)) {
                $fields = array_merge($fields, $arg);
            } else {
                $fields[] = $arg;
            }
        }

        $this->_source["exclude"] = array_unique(array_merge($this->_source["exclude"], $fields));
        $this->_source["include"] = array_values(array_filter($this->_source["include"], function ($field) {
            return !in_array($field, $this->_source["exclude"]);
        }));

        return $this;
    }

    /**
     * Filter by _id
     * @param mixed|bool $_id
     * @return $this
     */
    public function _id($_id = false)
    {

        $this->_id = $_id;

        $this->filter[] = ["term" => ["_id" => $_id]];

        return $this;
    }

    /**
     * Just an alias for _id() method
     * @param mixed|bool $_id
     * @return $this
     */
    public function id($_id = false)
    {
        return $this->_id($_id);
    }

    /**
     * Set the query where clause
     * @param        $name
     * @param string $operator
     * @param null $value
     * @return $this
     */
    public function where($name, $operator = "=", $value = NULL)
    {

        if (is_callback_function($name)) {
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
     * Set the query inverse where clause
     * @param        $name
     * @param string $operator
     * @param null $value
     * @return $this
     */
    public function whereNot($name, $operator = "=", $value = NULL)
    {

        if (is_callback_function($name)) {
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
     * Set the query where between clause
     * @param $name
     * @param $first_value
     * @param $last_value
     * @return $this
     */
    public function whereBetween($name, $first_value, $last_value = null)
    {

        if (is_array($first_value) && count($first_value) == 2) {
            $last_value = $first_value[1];
            $first_value = $first_value[0];
        }

        $this->filter[] = ["range" => [$name => ["gte" => $first_value, "lte" => $last_value]]];

        return $this;
    }

    /**
     * Set the query where not between clause
     * @param $name
     * @param $first_value
     * @param $last_value
     * @return $this
     */
    public function whereNotBetween($name, $first_value, $last_value = null)
    {

        if (is_array($first_value) && count($first_value) == 2) {
            $last_value = $first_value[1];
            $first_value = $first_value[0];
        }

        $this->must_not[] = ["range" => [$name => ["gte" => $first_value, "lte" => $last_value]]];

        return $this;
    }

    /**
     * Set the query where in clause
     * @param       $name
     * @param array $value
     * @return $this
     */
    public function whereIn($name, $value = [])
    {

        if (is_callback_function($name)) {
            $name($this);
            return $this;
        }

        $this->filter[] = ["terms" => [$name => $value]];

        return $this;
    }

    /**
     * Set the query where not in clause
     * @param       $name
     * @param array $value
     * @return $this
     */
    public function whereNotIn($name, $value = [])
    {

        if (is_callback_function($name)) {
            $name($this);
            return $this;
        }

        $this->must_not[] = ["terms" => [$name => $value]];

        return $this;
    }


    /**
     * Set the query where exists clause
     * @param      $name
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
     * Add a condition to find documents which are some distance away from the given geo point.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/2.4/query-dsl-geo-distance-query.html
     *
     * @param        $name
     *   A name of the field.
     * @param mixed $value
     *   A starting geo point which can be represented by a string "lat,lon",
     *   an object {"lat": lat, "lon": lon} or an array [lon,lat].
     * @param string $distance
     *   A distance from the starting geo point. It can be for example "20km".
     *
     * @return $this
     */
    public function distance($name, $value, $distance)
    {

        if (is_callback_function($name)) {
            $name($this);
            return $this;
        }

        $this->filter[] = [
            "geo_distance" => [
                $name => $value,
                "distance" => $distance,
            ]
        ];

        return $this;
    }

    /**
     * Search the entire document fields
     * @param null $q
     * @return $this
     */
    public function search($q = NULL, $settings = NULL)
    {

        if ($q) {

            $search = new Search($this, $q, $settings);

            if (!is_callback_function($settings)) {
                $search->boost($settings ? $settings : 1);
            }

            $search->build();

        }

        return $this;
    }

    public function nested($path, $query)
    {
        $this->body = [
            "query" => [
                "nested" => [
                    "path" => $path
                ]
            ]
        ];
    }

    /**
     * Get highlight result
     * @return $this
     */
    public function highlight()
    {

        $args = func_get_args();

        $fields = [];

        foreach ($args as $arg) {
            if (is_array($arg)) {
                $fields = array_merge($fields, $arg);
            } else {
                $fields[] = $arg;
            }
        }

        $new_fields = [];

        foreach ($fields as $field) {
            $new_fields[$field] = new \stdClass();
        }

        $this->body["highlight"] = [
            "fields" => $new_fields
        ];

        return $this;
    }

    /**
     * Generate the query body
     * @return array
     */
    protected function getBody()
    {

        $body = $this->body;

        if (count($this->_source)) {
            $_source = array_key_exists("_source", $body) ? $body["_source"] : [];
            $body["_source"] = array_merge($_source, $this->_source);
        }

        $body["query"] = isset($body["query"]) ? $body["query"]: [];

        if (count($this->must)) {
            $body["query"]["bool"]["must"] = $this->must;
        }

        if (count($this->must_not)) {
            $body["query"]["bool"]["must_not"] = $this->must_not;
        }

        if (count($this->filter)) {
            $body["query"]["bool"]["filter"] = $this->filter;
        }

        if(count($body["query"]) == 0){
            unset($body["query"]);
        }

//        $body = [
//            "query" => [
//                "nested" => [
//                    "path" => "pages",
//                    "query" => $body["query"],
//                    "inner_hits" => [
//                        "highlight" => [
//                            "fields" => [
//                                "pages.content" => (object) []
//                            ]
//                        ]
//                    ]
//                ]
//            ]
//        ];

        if (count($this->sort)) {
            $sortFields = array_key_exists("sort", $body) ? $body["sort"] : [];
            $body["sort"] = array_unique(array_merge($sortFields, $this->sort), SORT_REGULAR);
        }

        $this->body = $body;

        return $body;
    }

    /**
     * set the query body array
     * @param array $body
     * @return $this
     */
    function body($body = [])
    {

        $this->body = $body;

        return $this;
    }

    /**
     * Generate the query to be executed
     * @return array
     */
    public function query()
    {

        $query = [];

        $query["index"] = $this->getIndex();

        if ($this->getType()) {
            $query["type"] = $this->getType();
        }

        if ($this->model && $this->useGlobalScopes) {
            $this->model->boot($this);
        }

        $query["body"] = $this->getBody();

        $query["from"] = $this->getSkip();

        $query["size"] = $this->getTake();

        if (count($this->ignores)) {
            $query["client"] = ['ignore' => $this->ignores];
        }

        $search_type = $this->getSearchType();

        if ($search_type) {
            $query["search_type"] = $search_type;
        }

        $scroll = $this->getScroll();

        if ($scroll) {
            $query["scroll"] = $scroll;
        }

        return $query;
    }

    /**
     * Clear scroll query id
     * @param string $scroll_id
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

        $scroll_id = NULL;

        $result = $this->getResult($scroll_id);

        return $this->getAll($result);
    }

    /**
     * Get the first object of results
     * @param string $scroll_id
     * @return Model|object
     */
    public function first($scroll_id = NULL)
    {

        $this->take(1);

        $result = $this->getResult($scroll_id);

        return $this->getFirst($result);
    }

    /**
     * Get query result
     * @param $scroll_id
     * @return mixed
     */
    protected function getResult($scroll_id)
    {

        if (is_null($this->cacheMinutes)) {
            $result = $this->response($scroll_id);
        } else {

            $result = app("cache")->driver($this->cacheDriver)->get($this->getCacheKey());

            if (is_null($result)) {
                $result = $this->response($scroll_id);
            }
        }

        return $result;
    }


    /**
     * Get non cached results
     * @param null $scroll_id
     * @return mixed
     */
    public function response($scroll_id = NULL)
    {

        $scroll_id = !is_null($scroll_id) ? $scroll_id : $this->scroll_id;

        if ($scroll_id) {

            $result = $this->connection->scroll([
                "scroll" => $this->scroll,
                "scroll_id" => $scroll_id
            ]);

        } else {
            $result = $this->connection->search($this->query());
        }

        if (!is_null($this->cacheMinutes)) {
            app("cache")->driver($this->cacheDriver)->put($this->getCacheKey(), $result, $this->cacheMinutes);
        }

        return $result;
    }

    /**
     * Get the count of result
     * @return mixed
     */
    public function count()
    {

        $query = $this->query();

        // Remove unsupported count query keys

        unset(
            $query["size"],
            $query["from"],
            $query["body"]["_source"],
            $query["body"]["sort"]
        );

        return $this->connection->count($query)["count"];
    }

    /**
     * Set the query model
     * @param $model
     * @return $this
     */
    function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Retrieve all records
     * @param array $result
     * @return array|Collection
     */
    protected function getAll($result = [])
    {

        if (array_key_exists("hits", $result)) {

            $new = [];

            foreach ($result["hits"]["hits"] as $row) {

                $model = $this->model ? new $this->model($row["_source"], true) : new Model($row["_source"], true);

                $model->setConnection($model->getConnection());
                $model->setIndex($row["_index"]);
                $model->setType($row["_type"]);

                // match earlier version

                $model->_index = $row["_index"];
                $model->_type = $row["_type"];
                $model->_id = $row["_id"];
                $model->_score = $row["_score"];
                $model->_highlight = isset($row["highlight"]) ? $row["highlight"] : [];

                $new[] = $model;
            }

            $new = new Collection($new);

            $total = $result["hits"]["total"];

            $new->total = is_array($total) ? $total["value"] : $total;
            $new->max_score = $result["hits"]["max_score"];
            $new->took = $result["took"];
            $new->timed_out = $result["timed_out"];
            $new->scroll_id = isset($result["_scroll_id"]) ? $result["_scroll_id"] : NULL;
            $new->shards = (object)$result["_shards"];

            return $new;

        } else {
            return new Collection([]);
        }
    }

    /**
     * Retrieve only first record
     * @param array $result
     * @return Model|object
     */
    protected function getFirst($result = [])
    {

        if (array_key_exists("hits", $result) && count($result["hits"]["hits"])) {

            $data = $result["hits"]["hits"];

            if ($this->model) {
                $model = new $this->model($data[0]["_source"], true);
            } else {
                $model = new Model($data[0]["_source"], true);
                $model->setConnection($model->getConnection());
                $model->setIndex($data[0]["_index"]);
                $model->setType($data[0]["_type"]);
            }

            // match earlier version

            $model->_index = $data[0]["_index"];
            $model->_type = $data[0]["_type"];
            $model->_id = $data[0]["_id"];
            $model->_score = $data[0]["_score"];
            $model->_highlight = isset($data[0]["highlight"]) ? $data[0]["highlight"] : [];


            $new = $model;

        } else {
            $new = NULL;
        }

        return $new;
    }

    /**
     * Paginate collection of results
     * @param int $per_page
     * @param      $page_name
     * @param null $page
     * @return Pagination
     */
    public function paginate($per_page = 10, $page_name = "page", $page = null)
    {

        // Check if the request from PHP CLI
        if (php_sapi_name() == "cli") {
            $this->take($per_page);
            $page = $page ?: 1;
            $this->skip(($page * $per_page) - $per_page);
            $objects = $this->get();
            return new Pagination($objects, $objects->total, $per_page, $page);
        }

        $this->take($per_page);

        $page = $page ?: Request::get($page_name, 1);

        $this->skip(($page * $per_page) - $per_page);

        $objects = $this->get();

        return new Pagination($objects, $objects->total, $per_page, $page, ['path' => Request::url(), 'query' => Request::query()]);
    }

    /**
     * Insert a document
     * @param      $data
     * @param null $_id
     * @return object
     */
    public function insert($data, $_id = NULL)
    {

        if ($_id) {
            $this->_id = $_id;
        }

        $parameters = [
            "body" => $data,
            'client' => ['ignore' => $this->ignores]
        ];

        if ($index = $this->getIndex()) {
            $parameters["index"] = $index;
        }

        if ($type = $this->getType()) {
            $parameters["type"] = $type;
        }

        if ($this->_id) {
            $parameters["id"] = $this->_id;
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

        if (is_callback_function($data)) {

            $bulk = new Bulk($this);

            $data($bulk);

            $params = $bulk->body();

        } else {

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

        }

        return (object)$this->connection->bulk($params);
    }

    /**
     * Update a document
     * @param      $data
     * @param null $_id
     * @return object
     */
    public function update($data, $_id = NULL)
    {

        if ($_id) {
            $this->_id = $_id;
        }

        $parameters = [
            "id" => $this->_id,
            "body" => ['doc' => $data],
            'client' => ['ignore' => $this->ignores]
        ];

        if ($index = $this->getIndex()) {
            $parameters["index"] = $index;
        }

        if ($type = $this->getType()) {
            $parameters["type"] = $type;
        }

        return (object)$this->connection->update($parameters);
    }


    /**
     * Increment a document field
     * @param     $field
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
     * @param     $field
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
     * @param       $script
     * @param array $params
     * @return object
     */
    public function script($script, $params = [])
    {

        $parameters = [
            "id" => $this->_id,
            "body" => [
                "script" => [
                    "inline" => $script,
                    "params" => $params
                ]
            ],
            'client' => ['ignore' => $this->ignores]
        ];

        if ($index = $this->getIndex()) {
            $parameters["index"] = $index;
        }

        if ($type = $this->getType()) {
            $parameters["type"] = $type;
        }

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
            "id" => $this->_id,
            'client' => ['ignore' => $this->ignores]
        ];

        if ($index = $this->getIndex()) {
            $parameters["index"] = $index;
        }

        if ($type = $this->getType()) {
            $parameters["type"] = $type;
        }

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
     * Check existence of index
     * @return mixed
     */
    function exists()
    {

        $index = new Index($this->index);

        $index->connection = $this->connection;

        return $index->exists();
    }


    /**
     * Create a new index
     * @param      $name
     * @param bool $callback
     * @return mixed
     */
    function createIndex($name, $callback = false)
    {

        $index = new Index($name, $callback);

        $index->connection = $this->connection;

        return $index->create();
    }


    /**
     * Drop index
     * @param $name
     * @return mixed
     */
    function dropIndex($name)
    {

        $index = new Index($name);

        $index->connection = $this->connection;

        return $index->drop();
    }

    /**
     * create a new index [alias to createIndex method]
     * @param bool $callback
     * @return mixed
     */
    function create($callback = false)
    {

        $index = new Index($this->index, $callback);

        $index->connection = $this->connection;

        return $index->create();
    }

    /**
     * Drop index [alias to dropIndex method]
     * @return mixed
     */
    function drop()
    {

        $index = new Index($this->index);

        $index->connection = $this->connection;

        return $index->drop();
    }

    /* Caching Methods */

    /**
     * Indicate that the results, if cached, should use the given cache driver.
     *
     * @param string $cacheDriver
     *
     * @return $this
     */
    public function cacheDriver($cacheDriver)
    {

        $this->cacheDriver = $cacheDriver;

        return $this;
    }

    /**
     * Set the cache prefix.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function CachePrefix($prefix)
    {

        $this->cachePrefix = $prefix;

        return $this;
    }


    /**
     * Get a unique cache key for the complete query.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cachePrefix . ':' . ($this->cacheKey ?: $this->generateCacheKey());
    }


    /**
     * Generate the unique cache key for the query.
     * @return string
     */
    public function generateCacheKey()
    {

        return md5(json_encode($this->query()));
    }

    /**
     * Indicate that the query results should be cached.
     * @param \DateTime|int $minutes
     * @param string $key
     * @return $this
     */
    public function remember($minutes, $key = null)
    {

        list($this->cacheMinutes, $this->cacheKey) = [$minutes, $key];

        return $this;
    }

    /**
     * Indicate that the query results should be cached forever.
     * @param string $key
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function rememberForever($key = null)
    {
        return $this->remember(-1, $key);
    }

    /**
     * @param $method
     * @param $parameters
     * @return $this
     */
    function __call($method, $parameters)
    {

        if (method_exists($this, $method)) {
            return $this->$method(...$parameters);
        } else {

            // Check for model scopes

            $method = "scope" . ucfirst($method);

            if (method_exists($this->model, $method)) {
                $parameters = array_merge([$this], $parameters);
                $this->model->$method(...$parameters);
                return $this;
            }
        }

    }

    /**
     * @return $this
     */
    public function withoutGlobalScopes()
    {

        $this->useGlobalScopes = false;

        return $this;
    }
}
