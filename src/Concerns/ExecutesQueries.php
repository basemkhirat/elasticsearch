<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch\Concerns;

use DateTime;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\App;
use JsonException;
use Matchory\Elasticsearch\Classes\Bulk;
use Matchory\Elasticsearch\Collection;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;
use Matchory\Elasticsearch\Model;
use Matchory\Elasticsearch\Pagination;
use Matchory\Elasticsearch\Query;
use Matchory\Elasticsearch\Request;

use function array_diff_key;
use function array_flip;
use function array_map;
use function is_callable;
use function is_null;
use function md5;

use const PHP_SAPI;

trait ExecutesQueries
{
    /**
     * The key that should be used when caching the query.
     *
     * @var string|null
     */
    protected $cacheKey;

    /**
     * The number of seconds to cache the query.
     *
     * @var DateTime|int|null
     */
    protected $cacheTtl;

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
    protected $cachePrefix = Query::DEFAULT_CACHE_PREFIX;

    abstract public function getConnection(): ConnectionInterface;

    /**
     * Converts the current query into an array
     *
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * Converts the current query to JSON
     *
     * @return string
     * @throws JsonException
     */
    abstract public function toJson(): string;

    /**
     * @param string|null $id
     *
     * @return $this
     */
    abstract public function id(?string $id): self;

    abstract public function getIgnores(): array;

    abstract public function getScroll(): ?string;

    abstract public function getScrollId(): ?string;

    abstract public function getId(): ?string;

    abstract public function getIndex(): ?string;

    abstract public function getType(): ?string;

    abstract public function take(int $size): self;

    abstract public function skip(int $from): self;

    abstract public function getModel(): Model;

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
     * @throws JsonException
     */
    public function getCacheKey(): string
    {
        $cacheKey = $this->cacheKey ?: $this->generateCacheKey();

        return "{$this->cachePrefix}.{$cacheKey}";
    }

    /**
     * Generate the unique cache key for the query.
     *
     * @return string
     * @throws JsonException
     */
    public function generateCacheKey(): string
    {
        return md5($this->toJson());
    }

    /**
     * Indicate that the query results should be cached.
     *
     * @param DateTime|int $ttl Cache TTL in seconds.
     * @param string|null  $key Cache key to use. Will be generated
     *                          automatically if omitted.
     *
     * @return $this
     */
    public function remember($ttl, ?string $key = null): self
    {
        $this->cacheTtl = $ttl;
        $this->cacheKey = $key;

        return $this;
    }

    /**
     * Indicate that the query results should be cached forever.
     *
     * @param string|null $key
     *
     * @return $this
     */
    public function rememberForever(?string $key = null): self
    {
        return $this->remember(-1, $key);
    }

    /**
     * Get the collection of results
     *
     * @param string|null $scrollId
     *
     * @return Collection
     * @throws JsonException
     */
    public function get(?string $scrollId = null): Collection
    {
        $result = $this->getResult($scrollId);

        if ( ! $result) {
            return new Collection([]);
        }

        return $this->transformIntoCollection($result);
    }

    /**
     * Paginate collection of results
     *
     * @param int      $per_page
     * @param string   $page_name
     * @param int|null $page
     *
     * @return Pagination
     * @throws JsonException
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
            [
                'path' => Request::url(),
                'query' => Request::query(),
            ]
        );
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
        $scrollId = $scrollId ?? $this->getScrollId();

        return new Collection($this->getConnection()->clearScroll([
            'scroll_id' => $scrollId,
            'client' => ['ignore' => $this->getIgnores()],
        ]));
    }

    /**
     * Get the first object of results
     *
     * @param string|null $scroll_id
     *
     * @return Model|null
     * @throws JsonException
     */
    public function first(?string $scroll_id = null): ?Model
    {
        $this->take(1);

        $result = $this->getResult($scroll_id);

        if ( ! $result) {
            return null;
        }

        return $this->transformIntoModel($result);
    }

    /**
     * Insert a document
     *
     * @param mixed       $data
     * @param string|null $id
     *
     * @return object
     */
    public function insert($data, ?string $id = null): object
    {
        if ($id) {
            $this->id($id);
        }

        $parameters = [
            'body' => $data,
            'client' => ['ignore' => $this->getIgnores()],
        ];

        $parameters = $this->addBaseParams($parameters);

        if ($id = $this->getId()) {
            $parameters['id'] = $id;
        }

        return $this->getConnection()->insert($parameters);
    }

    /**
     * Update a document
     *
     * @param array       $attributes
     * @param string|null $id
     *
     * @return object
     */
    public function update(array $attributes, $id = null): object
    {
        if ($id) {
            $this->id($id);
        }

        unset(
            $attributes[self::FIELD_HIGHLIGHT],
            $attributes[self::FIELD_INDEX],
            $attributes[self::FIELD_SCORE],
            $attributes[self::FIELD_TYPE],
            $attributes[self::FIELD_ID],
        );

        $parameters = [
            'id' => $this->getId(),
            'body' => [
                'doc' => $attributes,
            ],
            'client' => [
                'ignore' => $this->getIgnores(),
            ],
        ];

        $parameters = $this->addBaseParams($parameters);

        return (object)$this->getConnection()->update($parameters);
    }

    /**
     * Delete a document
     *
     * @param string|null $id
     *
     * @return object
     */
    public function delete(?string $id = null): object
    {
        if ($id) {
            $this->id($id);
        }

        $parameters = [
            'id' => $this->getId(),
            'client' => ['ignore' => $this->getIgnores()],
        ];

        $parameters = $this->addBaseParams($parameters);

        return (object)$this->getConnection()->delete($parameters);
    }

    /**
     * Get the count of result
     *
     * @return int
     */
    public function count(): int
    {
        $query = $this->toArray();

        // Remove unsupported count query keys
        unset(
            $query[Query::PARAM_SIZE],
            $query[Query::PARAM_FROM],
            $query['body']['_source'],
            $query['body']['sort']
        );

        return (int)$this->getConnection()->count($query)['count'];
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
            'id' => $this->getId(),
            'body' => [
                'script' => [
                    'inline' => $script,
                    'params' => $params,
                ],
            ],
            'client' => ['ignore' => $this->getIgnores()],
        ];

        $parameters = $this->addBaseParams($parameters);

        return (object)$this->getConnection()->update($parameters);
    }

    /**
     * Increment a document field
     *
     * @param string $field
     * @param int    $count
     *
     * @return object
     */
    public function increment(string $field, int $count = 1): object
    {
        return $this->script("ctx._source.{$field} += params.count", [
            'count' => $count,
        ]);
    }

    /**
     * Increment a document field
     *
     * @param string $field
     * @param int    $count
     *
     * @return object
     */
    public function decrement(string $field, int $count = 1): object
    {
        return $this->script("ctx._source.{$field} -= params.count", [
            'count' => $count,
        ]);
    }

    /**
     * Insert multiple documents at once.
     *
     * @param array|callable $data Dictionary of [id => data] pairs
     *
     * @return object
     */
    public function bulk($data): object
    {
        if (is_callable($data)) {
            /** @var Query $this */
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

        return (object)$this->getConnection()->bulk($params);
    }

    /**
     * Get non-cached results
     *
     * @param string|null $scrollId
     *
     * @return array
     * @throws JsonException
     */
    public function performSearch(?string $scrollId = null): array
    {
        $scrollId = $scrollId ?? $this->getScrollId();
        $result = $scrollId
            ? $this->getConnection()->scroll([
                Query::PARAM_SCROLL => $this->getScroll(),
                Query::PARAM_SCROLL_ID => $scrollId,
            ])
            : $this->getConnection()->search($this->toArray());

        if ( ! is_null($this->cacheTtl)) {
            $this->getCache()->put(
                $this->getCacheKey(),
                $result,
                $this->cacheTtl
            );
        }

        return $result;
    }

    /**
     * Keeping around for backwards compatibility
     *
     * @return array
     * @deprecated Use toArray() instead
     * @see        Query::toArray()
     */
    public function query(): array
    {
        return $this->toArray();
    }

    /**
     * Executes the query and handles the result
     *
     * @param string|null $scrollId
     *
     * @return array|null
     * @throws JsonException
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    protected function getResult(?string $scrollId = null): ?array
    {
        if (is_null($this->cacheTtl)) {
            return $this->performSearch($scrollId);
        }

        $result = $this->getCache()->get($this->getCacheKey());

        if (is_null($result)) {
            return $this->performSearch($scrollId);
        }

        return $result;
    }

    /**
     * @return Cache
     * @todo         Make caching use dependency injection
     * @noinspection PhpDocMissingThrowsInspection
     */
    protected function getCache(): Cache
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return App::getFacadeApplication()
                  ->make(CacheManager::class)
                  ->driver($this->cacheDriver);
    }

    /**
     * Retrieves all documents from a response.
     *
     * @param array[] $response Response to extract documents from
     *
     * @return Collection Collection of model instances representing the
     *                    documents contained in the response
     */
    protected function transformIntoCollection(array $response = []): Collection
    {
        $items = array_map(function (array $document): Model {
            return $this->createModelInstance($document);
        }, $response[Query::FIELD_HITS][Query::FIELD_NESTED_HITS] ?? []);

        return Collection::fromResponse($response, $items);
    }

    /**
     * Retrieve the first document from a response. If the response does not
     * contain any hits, will return `null`.
     *
     * @param array[] $response Response to extract the first document from
     *
     * @return Model|null Model instance if any documents were found in the
     *                    response, `null` otherwise
     */
    protected function transformIntoModel(array $response = []): ?Model
    {
        if ( ! isset($response[Query::FIELD_HITS][Query::FIELD_NESTED_HITS][0])) {
            return null;
        }

        return $this->createModelInstance(
            $response[Query::FIELD_HITS][Query::FIELD_NESTED_HITS][0]
        );
    }

    /**
     * Processes a result and turns it into a model instance.
     *
     * @param array<string, mixed> $document Raw document to create a model
     *                                       instance from
     *
     * @return Model Model instance representing the source document
     */
    protected function createModelInstance(array $document): Model
    {
        $data = $document[Query::FIELD_SOURCE] ?? [];
        $metadata = array_diff_key($document, array_flip([
            Query::FIELD_SOURCE,
        ]));

        return $this->getModel()->newInstance(
            $data,
            $metadata,
            true,
            $document[Query::FIELD_INDEX] ?? null,
            $document[Query::FIELD_TYPE] ?? null,
        );
    }

    /**
     * Adds the base parameters required for all queries.
     *
     * @param array<string, mixed> $params Query parameters to hydrate
     *
     * @return array<string, mixed> Hydrated query parameters
     */
    private function addBaseParams(array $params): array
    {
        if ($index = $this->getIndex()) {
            $params[Query::PARAM_INDEX] = $index;
        }

        if ($type = $this->getType()) {
            $params[Query::PARAM_TYPE] = $type;
        }

        return $params;
    }
}
