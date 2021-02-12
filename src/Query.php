<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch;

use ArrayIterator;
use BadMethodCallException;
use Elasticsearch\Client;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Traits\ForwardsCalls;
use IteratorAggregate;
use JsonException;
use JsonSerializable;
use Matchory\Elasticsearch\Concerns\AppliesScopes;
use Matchory\Elasticsearch\Concerns\BuildsFluentQueries;
use Matchory\Elasticsearch\Concerns\ExecutesQueries;
use Matchory\Elasticsearch\Concerns\ExplainsQueries;
use Matchory\Elasticsearch\Concerns\ManagesIndices;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;

use function count;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * Query
 * =====
 * Query builder instance for Elasticsearch queries
 *
 * @package Matchory\Elasticsearch\Query
 * @todo    Rename to "Builder" for coherency with Eloquent. To avoid breaking
 *          changes, an alias should be registered for Query
 */
class Query implements Arrayable, JsonSerializable, Jsonable, IteratorAggregate
{
    use AppliesScopes;
    use BuildsFluentQueries;
    use ExecutesQueries;
    use ForwardsCalls;
    use ManagesIndices;
    use ExplainsQueries;

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

    protected const FIELD_QUERY = 'query';

    protected const FIELD_SCORE = '_score';

    protected const FIELD_SORT = 'sort';

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

    public const PARAM_CLIENT_IGNORE = 'ignore';

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
     * @var null
     * @deprecated Use getConnection()->getClient() to access the client instead
     * @see        ConnectionInterface::getClient()
     * @see        Query::getConnection()
     */
    public $client = null;

    /**
     * Elastic model instance.
     *
     * @var Model|null
     * @deprecated Use getModel() instead
     * @see        Query::getModel()
     */
    public $model;

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
     * Creates a new query builder instance.
     *
     * @param ConnectionInterface $connection Elasticsearch connection the query
     *                                        builder uses.
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;

        // We set a plain model here so there's always a model instance set.
        // This avoids errors in methods that rely on a model.
        $this->setModel(new Model());
    }

    /**
     * Retrieves the underlying Elasticsearch client instance. This can be used
     * to work with the Elasticsearch library directly. You should check out its
     * documentation for more information.
     *
     * @return Client Elasticsearch Client instance.
     * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/overview.html
     * @see Client
     */
    public function raw(): Client
    {
        return $this->getConnection()->getClient();
    }

    /**
     * Retrieves the underlying Elasticsearch connection.
     *
     * @return ConnectionInterface Connection instance.
     * @see ConnectionInterface
     * @see Connection
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * Converts the fluent query into an Elasticsearch query array that can be
     * converted into JSON.
     *
     * @inheritDoc
     */
    final public function toArray(): array
    {
        return $this->buildQuery();
    }

    /**
     * Converts the query to a JSON string.
     *
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
     * Retrieves the instance of the model the query is scoped to. It is set to
     * the model that initiated a query, but defaults to the Model class itself
     * if the query builder is used without models.
     *
     * @return Model Model instance used for the current query.
     */
    public function getModel(): Model
    {
        /** @noinspection PhpDeprecationInspection */
        return $this->model;
    }

    /**
     * Sets the model the query is based on. Any results will be casted to this
     * model. If no model is set, a plain model instance will be used.
     *
     * @param Model $model Model to use for the current query.
     *
     * @return $this Query builder instance for chaining.
     */
    public function setModel(Model $model): self
    {
        /** @noinspection PhpDeprecationInspection */
        $this->model = $model;

        return $this;
    }

    /**
     * Forwards calls to the model instance. If the called method is a scope,
     * it will be applied to the query.
     *
     * @param string $method     Name of the called method.
     * @param array  $parameters Parameters passed to the method.
     *
     * @return $this Query builder instance.
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $parameters): self
    {
        if ($this->hasNamedScope($method)) {
            return $this->callNamedScope($method, $parameters);
        }

        return $this->forwardCallTo(
            $this->getModel(),
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
     * Proxies to the collection iterator, allowing to iterate the query builder
     * directly as though it were a result collection.
     *
     * @inheritDoc
     */
    final public function getIterator(): ArrayIterator
    {
        return $this->get()->getIterator();
    }

    /**
     * Converts the query into an Elasticsearch query array.
     *
     * @return array
     */
    protected function buildQuery(): array
    {
        $params = [
            self::PARAM_BODY => $this->getBody(),
            self::PARAM_FROM => $this->getSkip(),
            self::PARAM_SIZE => $this->getSize(),
        ];

        if (count($this->getIgnores())) {
            $params[self::PARAM_CLIENT] = [
                self::PARAM_CLIENT_IGNORE => $this->ignores,
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
}
