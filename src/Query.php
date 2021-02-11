<?php
/** @noinspection PhpUnused, UnknownInspectionInspection */

declare(strict_types=1);

namespace Matchory\Elasticsearch;

use ArrayIterator;
use BadMethodCallException;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\ForwardsCalls;
use IteratorAggregate;
use JsonException;
use JsonSerializable;
use Matchory\Elasticsearch\Classes\Search;
use Matchory\Elasticsearch\Concerns\ExecutesQueries;
use Matchory\Elasticsearch\Concerns\ManagesIndices;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;
use Matchory\Elasticsearch\Interfaces\ScopeInterface;
use stdClass;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_unique;
use function array_unshift;
use function array_values;
use function count;
use function get_class;
use function in_array;
use function is_array;
use function is_callable;
use function is_int;
use function is_string;
use function json_encode;
use function method_exists;

use const JSON_THROW_ON_ERROR;
use const SORT_REGULAR;

/**
 * Class Query
 *
 * @package Matchory\Elasticsearch\Query
 */
class Query implements Arrayable, JsonSerializable, Jsonable, IteratorAggregate
{
    use ForwardsCalls;
    use ExecutesQueries;
    use ManagesIndices;

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

    public const PARAM_BODY = 'body';

    public const PARAM_CLIENT = 'client';

    public const PARAM_FROM = 'from';

    public const PARAM_INDEX = 'index';

    public const PARAM_SCROLL = 'scroll';

    public const PARAM_SCROLL_ID = 'scroll_id';

    public const PARAM_SEARCH_TYPE = 'search_type';

    public const PARAM_SIZE = 'size';

    public const PARAM_TYPE = 'type';

    public const SOURCE_EXCLUDE = 'exclude';

    public const SOURCE_INCLUDE = 'include';

    protected static $defaultSource = [
        'include' => [],
        'exclude' => [],
    ];

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
     * @var Model
     */
    public $model;

    /**
     * @var null
     * @deprecated Use getConnection()->getClient() to access the client instead
     * @see        ConnectionInterface::getClient()
     * @see        Query::getConnection()
     */
    public $client = null;

    /**
     * Elasticsearch connection instance
     * =================================
     * This connection instance will receive any unresolved method calls from
     * the query, effectively acting as a proxy: The connection itself proxies
     * to the Elasticsearch client instance.
     *
     * @var ConnectionInterface
     */
    protected $connection;

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
     * Index name
     * ==========
     * Name of the index to query. To search all data streams and indices in a
     * cluster, omit this parameter or use _all or *.
     * An index can be thought of as an optimized collection of documents and
     * each document is a collection of fields, which are the key-value pairs
     * that contain your data. By default, Elasticsearch indexes all data in
     * every field and each indexed field has a dedicated, optimized data
     * structure. For example, text fields are stored in inverted indices, and
     * numeric and geo fields are stored in BKD trees. The ability to use the
     * per-field data structures to assemble and return search results is what
     * makes Elasticsearch so fast.
     *
     * @var string|null
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.10/search-search.html
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.10/documents-indices.html
     */
    protected $index;

    /**
     * Mapping type
     * ============
     * Each document indexed is associated with a `_type` and an `_id`.
     * The `_type` field is indexed in order to make searching by type name fast
     * The value of the `_type` field is accessible in queries, aggregations,
     * scripts, and when sorting.
     * Note that mapping types are deprecated as of 6.0.0:
     * Indices created in Elasticsearch 7.0.0 or later no longer accept a
     * `_default_` mapping. Indices created in 6.x will continue to function as
     * before in Elasticsearch 6.x. Types are deprecated in APIs in 7.0, with
     * breaking changes to the index creation, put mapping, get mapping, put
     * template, get template and get field mappings APIs.
     *
     * @var string|null
     * @deprecated Mapping types are deprecated as of Elasticsearch 7.0.0
     * @see        https://www.elastic.co/guide/en/elasticsearch/reference/7.10/removal-of-types.html
     * @see        https://www.elastic.co/guide/en/elasticsearch/reference/7.10/mapping-type-field.html
     */
    protected $type;

    /**
     * Unique document ID
     * ==================
     * Each document has an `_id` that uniquely identifies it, which is indexed
     * so that documents can be looked up either with the GET API or the
     * `ids` query.
     * The `_id` can either be assigned at indexing time, or a unique `_id` can
     * be generated by Elasticsearch. This field is not configurable in
     * the mappings.
     *
     * The value of the `_id` field is accessible in queries such as `term`,
     * `terms`, `match`, and `query_string`.
     *
     * The `_id` field is restricted from use in aggregations, sorting, and
     * scripting. In case sorting or aggregating on the `_id` field is required,
     * it is advised to duplicate the content of the `_id` field into another
     * field that has `doc_values` enabled.
     *
     * @var string|null
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.10/mapping-id-field.html
     */
    protected $id;

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
     * Scroll
     * ======
     * While a search request returns a single “page” of results, the scroll API
     * can be used to retrieve large numbers of results (or even all results)
     * from a single search request, in much the same way as you would use a
     * cursor on a traditional database.
     *
     * Scrolling is not intended for real time user requests, but rather for
     * processing large amounts of data, e.g. in order to reindex the contents
     * of one index into a new index with a different configuration.
     *
     * The results that are returned from a scroll request reflect the state of
     * the index at the time that the initial search request was made, like a
     * snapshot in time. Subsequent changes to documents (index, update or
     * delete) will only affect later search requests.
     *
     * In order to use scrolling, the initial search request should specify the
     * scroll parameter in the query string, which tells Elasticsearch how long
     * it should keep the “search context” alive (see Keeping the search context
     * alive), eg ?scroll=1m.
     *
     * @var string
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.10/paginate-search-results.html#scroll-search-results
     */
    protected $scroll;

    /**
     * Scroll ID
     * =========
     * Identifier for the search and its search context.
     * You can use this scroll ID with the scroll API to retrieve the next batch
     * of search results for the request. See Scroll search results.
     * This parameter is only returned if the scroll query parameter is
     * specified in the request.
     *
     * @var string|null
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.10/paginate-search-results.html#scroll-search-results
     */
    protected $scrollId = null;

    /**
     * Search Type
     * ===========
     * There are different execution paths that can be done when executing a
     * distributed search. The distributed search operation needs to be
     * scattered to all the relevant shards and then all the results are
     * gathered back. When doing scatter/gather type execution, there are
     * several ways to do that, specifically with search engines.
     *
     * One of the questions when executing a distributed search is how much
     * results to retrieve from each shard. For example, if we have 10 shards,
     * the 1st shard might hold the most relevant results from 0 till 10, with
     * other shards results ranking below it. For this reason, when executing a
     * request, we will need to get results from 0 till 10 from all shards, sort
     * them, and then return the results if we want to ensure correct results.
     *
     * Another question, which relates to the search engine, is the fact that
     * each shard stands on its own. When a query is executed on a specific
     * shard, it does not take into account term frequencies and other search
     * engine information from the other shards. If we want to support accurate
     * ranking, we would need to first gather the term frequencies from all
     * shards to calculate global term frequencies, then execute the query on
     * each shard using these global frequencies.
     *
     * Also, because of the need to sort the results, getting back a large
     * document set, or even scrolling it, while maintaining the correct sorting
     * behavior can be a very expensive operation. For large result set
     * scrolling, it is best to sort by _doc if the order in which documents are
     * returned is not important.
     *
     * Elasticsearch is very flexible and allows to control the type of search
     * to execute on a per search request basis. The type can be configured by
     * setting the search_type parameter in the query string. The types are:
     *
     * Query Then Fetch
     * ----------------
     * Parameter value: `query_then_fetch`.
     *
     * Distributed term frequencies are calculated locally for each shard
     * running the search. We recommend this option for faster searches with
     * potentially less accurate scoring.
     *
     * This is the default setting, if you do not specify a `search_type` in
     * your request.
     *
     * Dfs, Query Then Fetch
     * ---------------------
     * Parameter value: `dfs_query_then_fetch`.
     *
     * Distributed term frequencies are calculated globally, using information
     * gathered from all shards running the search. While this option increases
     * the accuracy of scoring, it adds a round-trip to each shard, which can
     * result in slower searches.
     *
     * @var string
     * @psalm-var 'query_then_fetch'|'dfs_query_then_fetch'
     * @see       https://www.elastic.co/guide/en/elasticsearch/reference/7.10/search-search.html#search-type
     */
    protected $searchType;

    /**
     * Number of hits to return
     * ========================
     * Defines the number of hits to return. Defaults to `10`.
     *
     * By default, you cannot page through more than 10,000 hits using the
     * `from` and `size` parameters. To page through more hits, use the
     * `search_after` parameter.
     *
     * @var int
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.10/search-search.html#search-type
     */
    protected $size = self::DEFAULT_LIMIT;

    /**
     * Starting document offset
     * ========================
     * Starting document offset. Defaults to `0`.
     *
     * By default, you cannot page through more than 10,000 hits using the
     * `from` and `size` parameters. To page through more hits, use the
     * `search_after` parameter.
     *
     * @var int
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.10/search-search.html#search-type
     */
    protected $from = self::DEFAULT_OFFSET;

    /**
     * @var array<string, Closure|ScopeInterface>
     */
    protected $scopes;

    /**
     * @var array<string>
     */
    protected $removedScopes;

    /**
     * @param ConnectionInterface|null $connection Elasticsearch connection the
     *                                             query builds on
     */
    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->model = new Model();

        if ($connection) {
            $this->connection = $connection;
        }
    }

    /**
     * Sets the name of the index to use for the query.
     *
     * @param string|null $index
     *
     * @return $this
     */
    public function index(?string $index = null): self
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Sets the document mapping type to restrict the query to.
     *
     * @param string $type Name of the document mapping type
     *
     * @return $this
     * @deprecated Mapping types are deprecated as of Elasticsearch 6.0.0
     * @see        https://www.elastic.co/guide/en/elasticsearch/reference/7.10/removal-of-types.html
     */
    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
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
     * Sets the query scroll ID.
     *
     * @param string|null $scroll
     *
     * @return $this
     */
    public function scrollId(?string $scroll): self
    {
        $this->scrollId = $scroll;

        return $this;
    }

    /**
     * Set the query search type
     *
     * @param string $type
     *
     * @psalm-param 'query_then_fetch'|'dfs_query_then_fetch' $type
     *
     * @return $this
     * @see         https://www.elastic.co/guide/en/elasticsearch/reference/6.8/search-request-search-type.html
     */
    public function searchType(string $type): self
    {
        $this->searchType = $type;

        return $this;
    }

    /**
     * Retrieves the query search type
     *
     * @return string|null
     * @psalm-return 'query_then_fetch'|'dfs_query_then_fetch'
     * @see          https://www.elastic.co/guide/en/elasticsearch/reference/6.8/search-request-search-type.html
     */
    public function getSearchType(): ?string
    {
        return $this->searchType;
    }

    /**
     * Ignore bad HTTP response
     *
     * @param mixed ...$args
     *
     * @return $this
     */
    public function ignore(...$args): self
    {
        $this->ignores = array_merge(
            $this->ignores,
            $this->flattenArgs($args)
        );

        $this->ignores = array_unique($this->ignores);

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
     * @param mixed ...$args
     *
     * @return $this
     */
    public function select(...$args): self
    {
        $fields = $this->flattenArgs($args);

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
     * @param mixed ...$args
     *
     * @return $this
     */
    public function unselect(...$args): self
    {
        $fields = $this->flattenArgs($args);

        $this->source[self::SOURCE_EXCLUDE] = array_unique(array_merge(
            $this->source[self::SOURCE_EXCLUDE] ?? [],
            $fields
        ));

        $this->source[self::SOURCE_INCLUDE] = array_values(array_filter(
            $this->source[self::SOURCE_INCLUDE], function ($field) {
            return ! in_array(
                $field,
                $this->source[self::SOURCE_EXCLUDE] ?? [],
                false
            );
        }));

        return $this;
    }

    /**
     * @param string|null $id ID to filter by
     *
     * @return $this
     * @deprecated Use id() instead
     * @see        Query::id()
     */
    public function _id(?string $id = null): Query
    {
        return $this->id($id);
    }

    /**
     * Set the query where clause
     *
     * @param string|callable $name
     * @param string          $operator
     * @param mixed|null      $value
     *
     * @return $this
     */
    public function where(
        $name,
        $operator = self::OPERATOR_EQUAL,
        $value = null
    ): self {
        if (is_callable($name)) {
            $name($this);

            return $this;
        }

        if ( ! $this->isOperator((string)$operator)) {
            $value = $operator;
            $operator = self::OPERATOR_EQUAL;
        }

        switch ((string)$operator) {
            case self::OPERATOR_EQUAL:
                if ($name === self::FIELD_ID) {
                    return $this->id((string)$value);
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
                $this->whereExists($name, (bool)$value);
        }

        return $this;
    }

    /**
     * Set the query where clause and retrieve the first matching document.
     *
     * @param string|callable $name
     * @param string          $operator
     * @param mixed|null      $value
     *
     * @return Model|null
     */
    public function firstWhere(
        $name,
        $operator = self::OPERATOR_EQUAL,
        $value = null
    ): ?Model {
        return $this
            ->where($name, $operator, $value)
            ->first();
    }

    /**
     * Set the query inverse where clause
     *
     * @param string|callable $name
     * @param string          $operator
     * @param null            $value
     *
     * @return $this
     */
    public function whereNot(
        $name,
        $operator = self::OPERATOR_EQUAL,
        $value = null
    ): self {
        if (is_callable($name)) {
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
     * @param string $name
     * @param mixed  $firstValue
     * @param mixed  $lastValue
     *
     * @return $this
     */
    public function whereBetween(
        string $name,
        $firstValue,
        $lastValue = null
    ): self {
        if (is_array($firstValue) && count($firstValue) === 2) {
            [$firstValue, $lastValue] = $firstValue;
        }

        $this->filter[] = [
            'range' => [
                $name => [
                    'gte' => $firstValue,
                    'lte' => $lastValue,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Set the query where not between clause
     *
     * @param string     $name
     * @param mixed      $firstValue
     * @param mixed|null $lastValue
     *
     * @return $this
     */
    public function whereNotBetween(
        string $name,
        $firstValue,
        $lastValue = null
    ): self {
        if (is_array($firstValue) && count($firstValue) === 2) {
            [$firstValue, $lastValue] = $firstValue;
        }

        $this->must_not[] = [
            'range' => [
                $name => [
                    'gte' => $firstValue,
                    'lte' => $lastValue,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Set the query where in clause
     *
     * @param string|callable $name
     * @param array           $value
     *
     * @return $this
     */
    public function whereIn($name, $value = []): self
    {
        if (is_callable($name)) {
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
     * @param string|callable $name
     * @param array           $value
     *
     * @return $this
     */
    public function whereNotIn($name, $value = []): self
    {
        if (is_callable($name)) {
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
     * @param string|callable $name     A name of the field.
     * @param mixed           $value    A starting geo point which can be
     *                                  represented by a string 'lat,lon', an
     *                                  object like `{'lat': lat, 'lon': lon}`
     *                                  or an array like `[lon,lat]`.
     * @param string          $distance A distance from the starting geo point.
     *                                  It can be for example '20km'.
     *
     * @return $this
     */
    public function distance($name, $value, string $distance): self
    {
        if (is_callable($name)) {
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
     * @param string|null   $queryString
     * @param callable|null $settings
     * @param int|null      $boost
     *
     * @return $this
     */
    public function search(
        ?string $queryString = null,
        $settings = null,
        ?int $boost = null
    ): self {
        if ($queryString) {
            $search = new Search(
                $this,
                $queryString,
                $settings
            );

            $search->boost($boost ?? 1);
            $search->build();
        }

        return $this;
    }

    /**
     * @param string $path
     *
     * @return Query
     */
    public function nested(string $path): self
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
     * @param mixed ...$args
     *
     * @return $this
     */
    public function highlight(...$args): self
    {
        $fields = $this->flattenArgs($args);
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
     * Sets the query body
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
     * Set the collapse field
     *
     * @param string $field
     *
     * @return $this
     */
    public function groupBy(string $field): self
    {
        $this->body['collapse'] = [
            'field' => $field,
        ];

        return $this;
    }

    /**
     * Return the native client to execute native queries
     *
     * @return ConnectionInterface
     */
    public function raw(): ConnectionInterface
    {
        return $this->getConnection();
    }

    /**
     * Retrieves the underlying Elasticsearch client
     *
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * Retrieves the ID the query is restricted to.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Retrieves all ignored fields
     *
     * @return array
     */
    public function getIgnores(): array
    {
        return $this->ignores;
    }

    /**
     * Retrieves the name of the index used for the query.
     *
     * @return string|null
     */
    public function getIndex(): ?string
    {
        return $this->index;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Sets the model the query is based on. Any results will be casted to this
     * model. If no model is set, a plain model instance will be used.
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
     * Get the query scroll
     *
     * @return string|null
     */
    public function getScroll(): ?string
    {
        return $this->scroll;
    }

    public function getScrollId(): ?string
    {
        return $this->scrollId;
    }

    /**
     * Retrieves the document mapping type the query is restricted to.
     *
     * @return string|null
     * @deprecated Mapping types are deprecated as of Elasticsearch 6.0.0
     * @see        https://www.elastic.co/guide/en/elasticsearch/reference/7.10/removal-of-types.html
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Adds a term filter for the `_id` field.
     *
     * @param string|null $id
     *
     * @return $this
     */
    public function id(?string $id = null): self
    {
        $this->id = $id;
        $this->filter[] = [
            'term' => [
                self::FIELD_ID => $id,
            ],
        ];

        return $this;
    }

    /**
     * Set the query offset
     *
     * @param int $from
     *
     * @return $this
     */
    public function skip(int $from = 0): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Sets the number of hits to return from the result.
     *
     * @param int $size
     *
     * @return $this
     */
    public function take(int $size = 10): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $params = [
            self::PARAM_BODY => $this->getBody(),
            self::PARAM_FROM => $this->getSkip(),
            self::PARAM_SIZE => $this->getSize(),
        ];

        if (count($this->ignores)) {
            $params[self::PARAM_CLIENT] = [
                'ignore' => $this->ignores,
            ];
        }

        if ($searchType = $this->getSearchType()) {
            $params[self::PARAM_SEARCH_TYPE] = $searchType;
        }

        if ($scroll = $this->getScroll()) {
            $params[self::PARAM_SCROLL] = $scroll;
        }

        if ($index = $this->getIndex()) {
            $params[self::PARAM_INDEX] = $index;
        }

        if ($type = $this->getType()) {
            $params[self::PARAM_TYPE] = $type;
        }

        return $params;
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function toJson($options = 0): string
    {
        return json_encode(
            $this->jsonSerialize(),
            JSON_THROW_ON_ERROR | $options
        );
    }

    /**
     * @param string $method
     * @param array  $parameters
     *
     * @return $this
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $parameters): self
    {
        if ($this->hasNamedScope($method)) {
            return $this->callNamedScope($method, $parameters);
        }

        return $this->forwardCallTo(
            $this->model,
            $method,
            $parameters
        );
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Register a new global scope.
     *
     * @param string                 $identifier
     * @param ScopeInterface|Closure $scope
     *
     * @return $this
     */
    public function withGlobalScope(string $identifier, $scope): self
    {
        $this->scopes[$identifier] = $scope;

        if (method_exists($scope, 'extend')) {
            $scope->extend($this);
        }

        return $this;
    }

    /**
     * Remove a registered global scope.
     *
     * @param ScopeInterface|string $scope
     *
     * @return $this
     */
    public function withoutGlobalScope($scope): self
    {
        if ( ! is_string($scope)) {
            $scope = get_class($scope);
        }

        unset($this->scopes[$scope]);

        $this->removedScopes[] = $scope;

        return $this;
    }

    /**
     * Remove all or passed registered global scopes.
     *
     * @param ScopeInterface[]|null $scopes
     *
     * @return $this
     */
    public function withoutGlobalScopes(array $scopes = null): self
    {
        if ( ! is_array($scopes)) {
            $scopes = array_keys($this->scopes);
        }

        foreach ($scopes as $scope) {
            $this->withoutGlobalScope($scope);
        }

        return $this;
    }

    /**
     * Get an array of global scopes that were removed from the query.
     *
     * @return string[]
     */
    public function removedScopes(): array
    {
        return $this->removedScopes;
    }

    /**
     * Determine if the given model has a scope.
     *
     * @param string $scope
     *
     * @return bool
     */
    public function hasNamedScope(string $scope): bool
    {
        return $this->model && $this->model->hasNamedScope($scope);
    }

    /**
     * Call the given local model scopes.
     *
     * @param array|string $scopes
     *
     * @return $this
     */
    public function scopes($scopes): self
    {
        $query = $this;

        foreach (Arr::wrap($scopes) as $scope => $parameters) {
            // If the scope key is an integer, then the scope was passed as the
            // value and the parameter list is empty, so we will format the
            // scope name and these parameters here. Then, we'll be ready to
            // call the scope on the model.
            if (is_int($scope)) {
                [$scope, $parameters] = [$parameters, []];
            }

            // Next we'll pass the scope callback to the callScope method which
            // will take care of grouping the "wheres" properly so the logical
            // order doesn't get messed up when adding scopes.
            // Then we'll return back out the query.
            $query = $query->callNamedScope(
                $scope,
                (array)$parameters
            );
        }

        return $query;
    }

    /**
     * Apply the scopes to the Elasticsearch query instance and return it.
     *
     * @return $this
     */
    public function applyScopes(): self
    {
        if ( ! $this->scopes) {
            return $this;
        }

        $query = clone $this;

        foreach ($this->scopes as $identifier => $scope) {
            if ( ! isset($query->scopes[$identifier])) {
                continue;
            }

            $query->callScope(function (self $query) use ($scope) {
                // If the scope is a Closure we will just go ahead and call the
                // scope with the builder instance.
                if ($scope instanceof Closure) {
                    $scope($query);
                }

                // If the scope is a scope object, we will call the apply method
                // on this scope passing in the query and the model instance.
                // After we run all of these scopes we will return back the
                // query instance to the outside caller.
                if ($scope instanceof ScopeInterface) {
                    $scope->apply($query, $this->getModel());
                }
            });
        }

        return $query;
    }

    /**
     * Proxies to the collection iterator
     *
     * @inheritDoc
     */
    public function getIterator(): ArrayIterator
    {
        return $this->get()->getIterator();
    }

    /**
     * Apply the given scope on the current builder instance.
     *
     * @param callable $scope
     * @param array    $parameters
     *
     * @return $this
     */
    protected function callScope(callable $scope, array $parameters = []): self
    {
        array_unshift($parameters, $this);

        $scope(...array_values($parameters)) ?? $this;

        return $this;
    }

    /**
     * Apply the given named scope on the current query instance.
     *
     * @param string $scope
     * @param array  $parameters
     *
     * @return $this
     */
    protected function callNamedScope(
        string $scope,
        array $parameters = []
    ): Query {
        return $this->callScope(function (...$parameters) use ($scope) {
            return $this->model->callNamedScope(
                $scope,
                $parameters
            );
        }, $parameters);
    }

    /**
     * Get the query limit
     *
     * @return int
     * @deprecated Use getSize() instead
     */
    protected function getTake(): int
    {
        return $this->getSize();
    }

    /**
     * Retrieves the number of hits to limit the query to.
     *
     * @return int
     */
    protected function getSize(): int
    {
        return $this->size;
    }

    /**
     * Get the query offset
     *
     * @return int
     */
    protected function getSkip(): int
    {
        return $this->from;
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

            $body['sort'] = array_unique(
                array_merge($sortFields, $this->sort),
                SORT_REGULAR
            );
        }

        $this->body = $body;

        return $body;
    }

    private function flattenArgs(array $args): array
    {
        $flattened = [];

        foreach ($args as $arg) {
            if (is_array($arg)) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $flattened = array_merge($flattened, $arg);
            } else {
                $flattened[] = $arg;
            }
        }

        return $flattened;
    }
}
