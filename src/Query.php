<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch;

use DateTime;
use Elasticsearch\Client;
use Illuminate\Database\Query\Builder;
use Matchory\Elasticsearch\Classes\Bulk;
use Matchory\Elasticsearch\Classes\Search;
use stdClass;

use function array_filter;
use function array_key_exists;
use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function func_get_args;
use function in_array;
use function is_array;
use function is_callback_function;
use function is_null;
use function json_encode;
use function md5;
use function method_exists;
use function ucfirst;

use const PHP_SAPI;
use const SORT_REGULAR;

/**
 * Class Query
 *
 * @package Matchory\Elasticsearch\Query
 */
class Query
{

    public const DEFAULT_CACHE_PREFIX = 'es';

    public const DEFAULT_LIMIT = 10;

    public const DEFAULT_OFFSET = 0;

    public const EQ = self::OPERATOR_EQUAL;

    public const EXISTS = self::OPERATOR_EXISTS;

    protected const FIELD_HIGHLIGHT = '_highlight';

    protected const FIELD_HITS = 'hits';

    protected const FIELD_ID = '_id';

    protected const FIELD_INDEX = '_index';

    protected const FIELD_NESTED_HITS = 'hits';

    protected const FIELD_SCORE = '_score';

    protected const FIELD_SOURCE = '_source';

    protected const FIELD_TYPE = '_type';

    public const GT = self::OPERATOR_GREATER_THAN;

    public const GTE = self::OPERATOR_GREATER_THAN_OR_EQUAL;

    public const LIKE = self::OPERATOR_LIKE;

    public const LT = self::OPERATOR_LOWER_THAN;

    public const LTE = self::OPERATOR_LOWER_THAN_OR_EQUAL;

    public const NEQ = self::OPERATOR_NOT_EQUAL;

    public const OPERATOR_EQUAL = '=';

    public const OPERATOR_EXISTS = 'exists';

    public const OPERATOR_GREATER_THAN = '>';

    public const OPERATOR_GREATER_THAN_OR_EQUAL = '>=';

    public const OPERATOR_LIKE = 'like';

    public const OPERATOR_LOWER_THAN = '<';

    public const OPERATOR_LOWER_THAN_OR_EQUAL = '<=';

    public const OPERATOR_NOT_EQUAL = '!=';

    public const SOURCE_EXCLUDE = 'exclude';

    public const SOURCE_INCLUDE = 'include';

    protected static $defaultSource = [
        'include' => [],
        'exclude' => [],
    ];

    /**
     * Native elasticsearch connection instance
     *
     * @var Client
     */
    public $client;

    /**
     * Ignored HTTP errors
     *
     * @var array
     */
    public $ignores = [];

    /**
     * Query body
     *
     * @var array
     */
    public $body = [];

    /**
     * Query bool must
     *
     * @var array
     */
    public $must = [];

    /**
     * Query bool must not
     *
     * @var array
     */
    public $must_not = [];

    /**
     * Elastic model instance
     *
     * @var Model&string
     */
    public $model;

    /**
     * Use model global scopes
     *
     * @var bool
     */
    public $useGlobalScopes = true;

    /**
     * Filter operators
     *
     * @var array
     */
    protected $operators = [
        self::OPERATOR_EQUAL,
        self::OPERATOR_NOT_EQUAL,
        self::OPERATOR_GREATER_THAN,
        self::OPERATOR_GREATER_THAN_OR_EQUAL,
        self::OPERATOR_LOWER_THAN,
        self::OPERATOR_LOWER_THAN_OR_EQUAL,
        self::OPERATOR_LIKE,
        self::OPERATOR_EXISTS,
    ];

    /**
     * Query index name
     *
     * @var string|null
     */
    protected $index;

    /**
     * Query type name
     *
     * @var string|null
     */
    protected $type;

    /**
     * Query type key
     *
     * @var string|null
     */
    protected $_id;

    /**
     * Query bool filter
     *
     * @var array
     */
    protected $filter = [];

    /**
     * Query returned fields list
     *
     * @var array|null
     */
    protected $source;

    /**
     * Query sort fields
     *
     * @var array
     */
    protected $sort = [];

    /**
     * Query scroll time
     *
     * @var string
     */
    protected $scroll;

    /**
     * Query scroll id
     *
     * @var string
     */
    protected $scrollId;

    /**
     * Query search type
     *
     * @var int
     */
    protected $search_type;

    /**
     * Query limit
     *
     * @var int
     */
    protected $take = self::DEFAULT_LIMIT;

    /**
     * Query offset
     *
     * @var int
     */
    protected $skip = self::DEFAULT_OFFSET;

    /**
     * The key that should be used when caching the query.
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * The number of minutes to cache the query.
     *
     * @var int
     */
    protected $cacheMinutes;

    /**
     * The cache driver to be used.
     *
     * @var string
     */
    protected $cacheDriver;

    /**
     * A cache prefix.
     *
     * @var string
     */
    protected $cachePrefix = self::DEFAULT_CACHE_PREFIX;

    /**
     * Query constructor.
     *
     * @param Client|null $client
     */
    public function __construct(?Client $client = null)
    {
        $this->client = $client;
    }

    /**
     * Set the index name
     *
     * @param string $index
     *
     * @return $this
     */
    public function index(string $index): self
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Get the index name
     *
     * @return string|null
     */
    public function getIndex(): ?string
    {
        return $this->index;
    }

    /**
     * Set the type name
     *
     * @param string $type
     *
     * @return $this
     */
    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the type name
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set the query scroll
     *
     * @param string $scroll
     *
     * @return $this
     */
    public function scroll(string $scroll): self
    {
        $this->scroll = $scroll;

        return $this;
    }

    /**
     * Set the query scroll ID
     *
     * @param string $scroll
     *
     * @return $this
     */
    public function scrollID(string $scroll): self
    {
        $this->scrollId = $scroll;

        return $this;
    }

    /**
     * Set the query search type
     *
     * @param string $type
     *
     * @return $this
     */
    public function searchType(string $type): self
    {
        $this->search_type = $type;

        return $this;
    }

    /**
     * get the query search type
     *
     * @return int|null
     */
    public function getSearchType(): ?int
    {
        return $this->search_type;
    }

    /**
     * Get the query scroll
     *
     * @return string|null
     */
    public function getScroll(): ?string
    {
        return $this->scroll;
    }

    /**
     * Set the query limit
     *
     * @param int $take
     *
     * @return $this
     */
    public function take(int $take = 10): self
    {
        $this->take = $take;

        return $this;
    }

    /**
     * Ignore bad HTTP response
     *
     * @return $this
     */
    public function ignore(): self
    {
        $args = func_get_args();

        foreach ($args as $arg) {
            if (is_array($arg)) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $this->ignores = array_merge($this->ignores, $arg);
            } else {
                $this->ignores[] = $arg;
            }
        }

        $this->ignores = array_unique($this->ignores);

        return $this;
    }

    /**
     * Set the query offset
     *
     * @param int $skip
     *
     * @return $this
     */
    public function skip(int $skip = 0): self
    {
        $this->skip = $skip;

        return $this;
    }

    /**
     * Set the sorting field
     *
     * @param string|int $field
     * @param string     $direction
     *
     * @return $this
     */
    public function orderBy($field, string $direction = 'asc'): self
    {
        $this->sort[] = [$field => $direction];

        return $this;
    }

    /**
     * Set the query fields to return
     *
     * @return $this
     */
    public function select(): self
    {
        $args = func_get_args();

        $fields = [];

        foreach ($args as $arg) {
            if (is_array($arg)) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $fields = array_merge($fields, $arg);
            } else {
                $fields[] = $arg;
            }
        }

        $this->source['include'] = array_unique(array_merge(
            $this->source['include'] ?? [],
            $fields
        ));

        $this->source['exclude'] = array_values(array_filter(
            $this->source['exclude'] ?? [], function ($field) {
            return ! in_array(
                $field,
                $this->source['include'],
                false
            );
        }));

        return $this;
    }

    /**
     * Set the ignored fields to not be returned
     *
     * @return $this
     */
    public function unselect(): self
    {
        $args = func_get_args();

        $fields = [];

        foreach ($args as $arg) {
            if (is_array($arg)) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $fields = array_merge($fields, $arg);
            } else {
                $fields[] = $arg;
            }
        }

        $this->source[self::SOURCE_EXCLUDE] = array_unique(array_merge(
            $this->source[self::SOURCE_EXCLUDE],
            $fields
        ));

        $this->source[self::SOURCE_INCLUDE] = array_values(array_filter(
            $this->source[self::SOURCE_INCLUDE], function ($field) {
            return ! in_array(
                $field,
                $this->source[self::SOURCE_EXCLUDE],
                false
            );
        }));

        return $this;
    }

    /**
     * Filter by _id
     *
     * @param string|null $_id
     *
     * @return $this
     */
    public function _id(?string $_id = null): Query
    {
        $this->_id = $_id;
        $this->filter[] = [
            'term' => [
                '_id' => $_id,
            ],
        ];

        return $this;
    }

    /**
     * Just an alias for _id() method
     *
     * @param string|null $_id
     *
     * @return $this
     */
    public function id(?string $_id = null): self
    {
        return $this->_id($_id);
    }

    /**
     * Set the query where clause
     *
     * @param string     $name
     * @param string     $operator
     * @param mixed|null $value
     *
     * @return $this
     */
    public function where(
        string $name,
        $operator = self::OPERATOR_EQUAL,
        $value = null
    ): self {
        if (is_callback_function($name)) {
            $name($this);

            return $this;
        }

        if ( ! $this->isOperator((string)$operator)) {
            $value = $operator;
            $operator = self::OPERATOR_EQUAL;
        }

        switch ((string)$operator) {
            case self::OPERATOR_EQUAL:
                if ($name === '_id') {
                    return $this->_id($value);
                }

                $this->filter[] = ['term' => [$name => $value]];
                break;

            case self::OPERATOR_GREATER_THAN:
                $this->filter[] = ['range' => [$name => ['gt' => $value]]];
                break;

            case self::OPERATOR_GREATER_THAN_OR_EQUAL:
                $this->filter[] = ['range' => [$name => ['gte' => $value]]];
                break;

            case self::OPERATOR_LOWER_THAN:
                $this->filter[] = ['range' => [$name => ['lt' => $value]]];
                break;

            case self::OPERATOR_LOWER_THAN_OR_EQUAL:
                $this->filter[] = ['range' => [$name => ['lte' => $value]]];
                break;

            case self::OPERATOR_LIKE:
                $this->must[] = ['match' => [$name => $value]];
                break;

            case self::OPERATOR_EXISTS:
                $this->whereExists($name, $value);
        }

        return $this;
    }

    /**
     * Set the query inverse where clause
     *
     * @param        $name
     * @param string $operator
     * @param null   $value
     *
     * @return $this
     */
    public function whereNot(
        $name,
        $operator = self::OPERATOR_EQUAL,
        $value = null
    ): self {
        if (is_callback_function($name)) {
            $name($this);

            return $this;
        }

        if ( ! $this->isOperator($operator)) {
            $value = $operator;
            $operator = self::OPERATOR_EQUAL;
        }

        switch ($operator) {
            case self::OPERATOR_EQUAL:
                $this->must_not[] = ['term' => [$name => $value]];
                break;

            case self::OPERATOR_GREATER_THAN:
                $this->must_not[] = ['range' => [$name => ['gt' => $value]]];
                break;

            case self::OPERATOR_GREATER_THAN_OR_EQUAL:
                $this->must_not[] = ['range' => [$name => ['gte' => $value]]];
                break;

            case self::OPERATOR_LOWER_THAN:
                $this->must_not[] = ['range' => [$name => ['lt' => $value]]];
                break;

            case self::OPERATOR_LOWER_THAN_OR_EQUAL:
                $this->must_not[] = ['range' => [$name => ['lte' => $value]]];
                break;

            case self::OPERATOR_LIKE:
                $this->must_not[] = ['match' => [$name => $value]];
                break;

            case self::OPERATOR_EXISTS:
                $this->whereExists($name, ! $value);
        }

        return $this;
    }

    /**
     * Set the query where between clause
     *
     * @param $name
     * @param $first_value
     * @param $last_value
     *
     * @return $this
     */
    public function whereBetween($name, $first_value, $last_value = null): self
    {
        if (is_array($first_value) && count($first_value) === 2) {
            [$first_value, $last_value] = $first_value;
        }

        $this->filter[] = [
            'range' => [
                $name => [
                    'gte' => $first_value,
                    'lte' => $last_value,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Set the query where not between clause
     *
     * @param $name
     * @param $first_value
     * @param $last_value
     *
     * @return $this
     */
    public function whereNotBetween(
        $name,
        $first_value,
        $last_value = null
    ): self {
        if (is_array($first_value) && count($first_value) === 2) {
            [$first_value, $last_value] = $first_value;
        }

        $this->must_not[] = [
            'range' => [
                $name => [
                    'gte' => $first_value,
                    'lte' => $last_value,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Set the query where in clause
     *
     * @param       $name
     * @param array $value
     *
     * @return $this
     */
    public function whereIn($name, $value = []): self
    {
        if (is_callback_function($name)) {
            $name($this);

            return $this;
        }

        $this->filter[] = [
            'terms' => [$name => $value],
        ];

        return $this;
    }

    /**
     * Set the query where not in clause
     *
     * @param       $name
     * @param array $value
     *
     * @return $this
     */
    public function whereNotIn($name, $value = []): self
    {
        if (is_callback_function($name)) {
            $name($this);

            return $this;
        }

        $this->must_not[] = [
            'terms' => [$name => $value],
        ];

        return $this;
    }

    /**
     * Set the query where exists clause
     *
     * @param string $name
     * @param bool   $exists
     *
     * @return $this
     */
    public function whereExists(string $name, bool $exists = true): self
    {
        if ($exists) {
            $this->must[] = [
                'exists' => ['field' => $name],
            ];
        } else {
            $this->must_not[] = [
                'exists' => ['field' => $name],
            ];
        }

        return $this;
    }

    /**
     * Add a condition to find documents which are some distance away from the
     * given geo point.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/2.4/query-dsl-geo-distance-query.html
     *
     * @param string $name     A name of the field.
     * @param mixed  $value    A starting geo point which can be represented by
     *                         a string 'lat,lon', an object like
     *                         `{'lat': lat, 'lon': lon}` or an array
     *                         like `[lon,lat]`.
     * @param string $distance A distance from the starting geo point. It can be
     *                         for example '20km'.
     *
     * @return $this
     */
    public function distance(string $name, $value, string $distance): self
    {
        if (is_callback_function($name)) {
            $name($this);

            return $this;
        }

        $this->filter[] = [
            'geo_distance' => [
                $name => $value,
                'distance' => $distance,
            ],
        ];

        return $this;
    }

    /**
     * Search the entire document fields
     *
     * @param string|null       $queryString
     * @param callable|int|null $settings
     *
     * @return $this
     */
    public function search(?string $queryString = null, $settings = null): self
    {
        if ($queryString) {
            $search = new Search(
                $this,
                $queryString,
                $settings
            );

            if ( ! is_callback_function($settings)) {
                $search->boost($settings ?: 1);
            }

            $search->build();
        }

        return $this;
    }

    /**
     * @param $path
     *
     * @return Query
     */
    public function nested($path): self
    {
        $this->body = [
            'query' => [
                'nested' => [
                    'path' => $path,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Get highlight result
     *
     * @return $this
     */
    public function highlight(): self
    {
        $args = func_get_args();

        $fields = [];

        foreach ($args as $arg) {
            if (is_array($arg)) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $fields = array_merge($fields, $arg);
            } else {
                $fields[] = $arg;
            }
        }

        $new_fields = [];

        foreach ($fields as $field) {
            $new_fields[$field] = new stdClass();
        }

        $this->body['highlight'] = [
            'fields' => $new_fields,
        ];

        return $this;
    }

    /**
     * set the query body array
     *
     * @param array $body
     *
     * @return $this
     */
    public function body(array $body = []): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Generate the query to be executed
     *
     * @return array
     */
    public function query(): array
    {
        $query = [];

        $query['index'] = $this->getIndex();

        if ($this->getType()) {
            $query['type'] = $this->getType();
        }

        // TODO: What should be happening here?
        if ($this->model && $this->useGlobalScopes) {
            $this->model->boot($this);
        }

        $query['body'] = $this->getBody();
        $query['from'] = $this->getSkip();
        $query['size'] = $this->getTake();

        if (count($this->ignores)) {
            $query['client'] = ['ignore' => $this->ignores];
        }

        $search_type = $this->getSearchType();

        if ($search_type) {
            $query['search_type'] = $search_type;
        }

        $scroll = $this->getScroll();

        if ($scroll) {
            $query['scroll'] = $scroll;
        }

        return $query;
    }

    /**
     * Clear scroll query id
     *
     * @param string|null $scrollId
     *
     * @return Collection
     */
    public function clear(?string $scrollId = null): Collection
    {
        $scrollId = $scrollId ?? $this->scrollId;

        return new Collection($this->client->clearScroll([
            'scroll_id' => $scrollId,
            'client' => ['ignore' => $this->ignores],
        ]));
    }

    /**
     * Get the collection of results
     *
     * @param string|null $scrollId
     *
     * @return Collection
     */
    public function get(?string $scrollId = null): Collection
    {
        $result = $this->getResult($scrollId);

        return $this->getAll($result);
    }

    /**
     * Get the first object of results
     *
     * @param string|null $scroll_id
     *
     * @return Model|object
     */
    public function first(?string $scroll_id = null)
    {
        $this->take(1);

        $result = $this->getResult($scroll_id);

        return $this->getFirst($result);
    }

    /**
     * Get non-cached results
     *
     * @param string|null $scrollId
     *
     * @return array
     */
    public function response(?string $scrollId = null): array
    {
        $scrollId = $scrollId ?? $this->scrollId;

        if ($scrollId) {
            $result = $this->client->scroll([
                'scroll' => $this->scroll,
                'scroll_id' => $scrollId,
            ]);
        } else {
            $result = $this->client->search($this->query());
        }

        if ( ! is_null($this->cacheMinutes)) {
            app('cache')
                ->driver($this->cacheDriver)
                ->put($this->getCacheKey(), $result, $this->cacheMinutes);
        }

        return $result;
    }

    /**
     * Get the count of result
     *
     * @return int
     */
    public function count(): int
    {
        $query = $this->query();

        // Remove unsupported count query keys
        unset(
            $query['size'],
            $query['from'],
            $query['body']['_source'],
            $query['body']['sort']
        );

        return (int)$this->client->count($query)['count'];
    }

    /**
     * Set the query model
     *
     * @param Model $model
     *
     * @return $this
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Paginate collection of results
     *
     * @param int      $per_page
     * @param string   $page_name
     * @param int|null $page
     *
     * @return Pagination
     */
    public function paginate(
        int $per_page = 10,
        string $page_name = 'page',
        ?int $page = null
    ): Pagination {
        // Check if the request from PHP CLI
        if (PHP_SAPI === 'cli') {
            $this->take($per_page);
            $page = $page ?: 1;
            $this->skip(($page * $per_page) - $per_page);
            $collection = $this->get();

            return new Pagination(
                $collection,
                $collection->getTotal() ?? 0,
                $per_page,
                $page
            );
        }

        $this->take($per_page);

        $page = $page ?: Request::get($page_name, 1);

        $this->skip(($page * $per_page) - $per_page);

        $collection = $this->get();

        return new Pagination(
            $collection,
            $collection->getTotal() ?? 0,
            $per_page,
            $page,
            ['path' => Request::url(), 'query' => Request::query()]
        );
    }

    /**
     * Insert a document
     *
     * @param mixed       $data
     * @param string|null $_id
     *
     * @return object
     */
    public function insert($data, ?string $_id = null): object
    {
        if ($_id) {
            $this->_id = $_id;
        }

        $parameters = [
            'body' => $data,
            'client' => ['ignore' => $this->ignores],
        ];

        if ($index = $this->getIndex()) {
            $parameters['index'] = $index;
        }

        if ($type = $this->getType()) {
            $parameters['type'] = $type;
        }

        if ($this->_id) {
            $parameters['id'] = $this->_id;
        }

        return (object)$this->client->index($parameters);
    }

    /**
     * Insert a bulk of documents
     *
     * @param array|callable $data multidimensional array of [id => data] pairs
     *
     * @return object
     */
    public function bulk($data): object
    {
        if (is_callback_function($data)) {
            $bulk = new Bulk($this);

            $data($bulk);

            $params = $bulk->body();
        } else {
            $params = [];

            foreach ($data as $key => $value) {
                $params['body'][] = [

                    'index' => [
                        '_index' => $this->getIndex(),
                        '_type' => $this->getType(),
                        '_id' => $key,
                    ],

                ];

                $params['body'][] = $value;
            }
        }

        return (object)$this->client->bulk($params);
    }

    /**
     * Update a document
     *
     * @param mixed       $data
     * @param string|null $_id
     *
     * @return object
     */
    public function update($data, $_id = null): object
    {
        if ($_id) {
            $this->_id = $_id;
        }

        $parameters = [
            'id' => $this->_id,
            'body' => ['doc' => $data],
            'client' => ['ignore' => $this->ignores],
        ];

        if ($index = $this->getIndex()) {
            $parameters['index'] = $index;
        }

        if ($type = $this->getType()) {
            $parameters['type'] = $type;
        }

        return (object)$this->client->update($parameters);
    }

    /**
     * Increment a document field
     *
     * @param mixed $field
     * @param int   $count
     *
     * @return object
     */
    public function increment($field, int $count = 1): object
    {
        return $this->script("ctx._source.{$field} += params.count", [
            'count' => $count,
        ]);
    }

    /**
     * Increment a document field
     *
     * @param mixed $field
     * @param int   $count
     *
     * @return object
     */
    public function decrement($field, int $count = 1): object
    {
        return $this->script("ctx._source.{$field} -= params.count", [
            'count' => $count,
        ]);
    }

    /**
     * Update by script
     *
     * @param mixed $script
     * @param array $params
     *
     * @return object
     */
    public function script($script, array $params = []): object
    {
        $parameters = [
            'id' => $this->_id,
            'body' => [
                'script' => [
                    'inline' => $script,
                    'params' => $params,
                ],
            ],
            'client' => ['ignore' => $this->ignores],
        ];

        if ($index = $this->getIndex()) {
            $parameters['index'] = $index;
        }

        if ($type = $this->getType()) {
            $parameters['type'] = $type;
        }

        return (object)$this->client->update($parameters);
    }

    /**
     * Delete a document
     *
     * @param string|null $_id
     *
     * @return object
     */
    public function delete(?string $_id = null): object
    {
        if ($_id) {
            $this->_id = $_id;
        }

        $parameters = [
            'id' => $this->_id,
            'client' => ['ignore' => $this->ignores],
        ];

        if ($index = $this->getIndex()) {
            $parameters['index'] = $index;
        }

        if ($type = $this->getType()) {
            $parameters['type'] = $type;
        }

        return (object)$this->client->delete($parameters);
    }

    /**
     * Return the native client to execute native queries
     *
     * @return Client
     */
    public function raw(): Client
    {
        return $this->client;
    }

    /**
     * Check existence of index
     *
     * @return bool
     */
    public function exists(): bool
    {
        $index = new Index($this->index);

        $index->setClient($this->client);

        return $index->exists();
    }

    /**
     * Create a new index
     *
     * @param string        $name
     * @param callable|null $callback
     *
     * @return array
     */
    public function createIndex(string $name, ?callable $callback = null): array
    {
        $index = new Index($name, $callback);

        $index->client = $this->client;

        return $index->create();
    }

    /**
     * Drop index
     *
     * @param $name
     *
     * @return array
     */
    public function dropIndex($name): array
    {
        $index = new Index($name);

        $index->client = $this->client;

        return $index->drop();
    }

    /**
     * create a new index [alias to createIndex method]
     *
     * @param callable|null $callback
     *
     * @return array
     */
    public function create(?callable $callback = null): array
    {
        $index = new Index($this->index, $callback);
        $index->client = $this->client;

        return $index->create();
    }

    /**
     * Drop index [alias to dropIndex method]
     *
     * @return array
     */
    public function drop(): array
    {
        $index = new Index($this->index);

        $index->client = $this->client;

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
    public function cacheDriver(string $cacheDriver): self
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
    public function cachePrefix(string $prefix): self
    {
        $this->cachePrefix = $prefix;

        return $this;
    }

    /**
     * Get a unique cache key for the complete query.
     *
     * @return string
     */
    public function getCacheKey(): string
    {
        $cacheKey = $this->cacheKey ?: $this->generateCacheKey();

        return "{$this->cachePrefix}:{$cacheKey}";
    }

    /**
     * Generate the unique cache key for the query.
     *
     * @return string
     */
    public function generateCacheKey(): string
    {
        return md5(json_encode($this->query()));
    }

    /**
     * Indicate that the query results should be cached.
     *
     * @param DateTime|int $minutes
     * @param string|null  $key
     *
     * @return $this
     */
    public function remember($minutes, ?string $key = null): self
    {
        [$this->cacheMinutes, $this->cacheKey] = [$minutes, $key];

        return $this;
    }

    /**
     * Indicate that the query results should be cached forever.
     *
     * @param string|null $key
     *
     * @return Builder|static
     */
    public function rememberForever(?string $key = null)
    {
        return $this->remember(-1, $key);
    }

    /**
     * @param string $method
     * @param array  $parameters
     *
     * @return $this
     */
    public function __call(string $method, array $parameters): self
    {
        // Check for model scopes
        $method = 'scope' . ucfirst($method);

        if (method_exists($this->model, $method)) {
            $parameters = array_merge([$this], $parameters);
            $this->model->$method(...$parameters);

            return $this;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function withoutGlobalScopes(): self
    {
        $this->useGlobalScopes = false;

        return $this;
    }

    /**
     * Get the query limit
     *
     * @return int
     */
    protected function getTake(): int
    {
        return $this->take;
    }

    /**
     * Get the query offset
     *
     * @return int
     */
    protected function getSkip(): int
    {
        return $this->skip;
    }

    /**
     * check if it's a valid operator
     *
     * @param $string
     *
     * @return bool
     */
    protected function isOperator(string $string): bool
    {
        return in_array(
            $string,
            $this->operators,
            true
        );
    }

    /**
     * Generate the query body
     *
     * @return array
     */
    protected function getBody(): array
    {
        $body = $this->body;

        if ($this->source !== null) {
            $source = $body[self::FIELD_SOURCE] ?? [];

            // TODO: Shouldn't the body-defined source take precedence here?
            $body[self::FIELD_SOURCE] = array_merge(
                $source,
                $this->source
            );
        }

        $body['query'] = $body['query'] ?? [];

        if (count($this->must)) {
            $body['query']['bool']['must'] = $this->must;
        }

        if (count($this->must_not)) {
            $body['query']['bool']['must_not'] = $this->must_not;
        }

        if (count($this->filter)) {
            $body['query']['bool']['filter'] = $this->filter;
        }

        if (count($body['query']) === 0) {
            unset($body['query']);
        }

        if (count($this->sort)) {
            $sortFields = array_key_exists('sort', $body)
                ? $body['sort']
                : [];

            if (isset($body['sort'])) {
                $body['sort'] = array_unique(
                    array_merge($sortFields, $this->sort),
                    SORT_REGULAR
                );
            }
        }

        $this->body = $body;

        return $body;
    }

    /**
     * Get query result
     *
     * @param string|null $scrollId
     *
     * @return array|null
     */
    protected function getResult(?string $scrollId = null): ?array
    {
        if (is_null($this->cacheMinutes)) {
            return $this->response($scrollId);
        }

        $result = app('cache')
            ->driver($this->cacheDriver)
            ->get($this->getCacheKey());

        if (is_null($result)) {
            return $this->response($scrollId);
        }

        return $result;
    }

    /**
     * Retrieve all records
     *
     * @param array $result
     *
     * @return Collection
     */
    protected function getAll(array $result = []): Collection
    {
        if ( ! array_key_exists(self::FIELD_HITS, $result)) {
            return new Collection([]);
        }

        $items = [];

        foreach ($result[self::FIELD_HITS][self::FIELD_NESTED_HITS] as $row) {
            // Fallback to default model class
            $modelClass = $this->model ?? Model::class;
            $model = new $modelClass($row[self::FIELD_SOURCE], true);

            $model->setConnection($model->getConnection());
            $model->setIndex($row[self::FIELD_INDEX]);
            $model->setType($row[self::FIELD_TYPE]);

            // match earlier version
            $model->_index = $row[self::FIELD_INDEX];
            $model->_type = $row[self::FIELD_TYPE];
            $model->_id = $row[self::FIELD_ID];
            $model->_score = $row[self::FIELD_SCORE];
            $model->_highlight = $row[self::FIELD_HIGHLIGHT] ?? [];

            $items[] = $model;
        }

        return Collection::fromResponse($result, $items);
    }

    /**
     * Retrieve only first record
     *
     * @param array $result
     *
     * @return Model|null
     */
    protected function getFirst(array $result = []): ?Model
    {
        if (
            ! array_key_exists(self::FIELD_HITS, $result) ||
            ! count($result[self::FIELD_HITS][self::FIELD_NESTED_HITS])
        ) {
            return null;
        }

        $data = $result[self::FIELD_HITS][self::FIELD_NESTED_HITS];
        $modelClass = $this->model ?? Model::class;

        /** @var Model $model */
        $model = new $modelClass($data[0][self::FIELD_SOURCE], true);

        $model->setConnection($model->getConnection());
        $model->setIndex($data[0][self::FIELD_INDEX]);
        $model->setType($data[0][self::FIELD_TYPE]);

        // match earlier version
        $model->_index = $data[0][self::FIELD_INDEX];
        $model->_type = $data[0][self::FIELD_TYPE];
        $model->_id = $data[0][self::FIELD_ID];
        $model->_score = $data[0][self::FIELD_SCORE];
        $model->_highlight = $data[0][self::FIELD_HIGHLIGHT] ?? [];

        return $model;
    }
}
