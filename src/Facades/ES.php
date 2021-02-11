<?php

namespace Matchory\Elasticsearch\Facades;

use Illuminate\Support\Facades\Facade;
use Matchory\Elasticsearch\Collection;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionResolverInterface;
use Matchory\Elasticsearch\Model;
use Matchory\Elasticsearch\Pagination;
use Matchory\Elasticsearch\Query;

/**
 * Elasticsearch Facade
 * ====================
 * This facade proxies to the default connection instance, which in turn proxies
 * to a new query instance. This provides unified access to almost all methods
 * the library has to offer.
 *
 * @method static ConnectionInterface connection(?string $name = null)
 * @method static string getDefaultConnection()
 * @method static void setDefaultConnection(string $name)
 * @method static Query newQuery()
 * @method static Query index(string $index)
 * @method static Query scroll(string $scroll)
 * @method static Query scrollId(?string $scroll)
 * @method static Query searchType(string $type)
 * @method static Query ignore(...$args)
 * @method static Query orderBy($field, string $direction = 'asc')
 * @method static Query select(...$args)
 * @method static Query unselect(...$args)
 * @method static Query where($name, $operator = Query::OPERATOR_EQUAL, $value = null)
 * @method static Query firstWhere($name, $operator = Query::OPERATOR_EQUAL, $value = null)
 * @method static Query whereNot($name, $operator = Query::OPERATOR_EQUAL, $value = null)
 * @method static Query whereBetween($name, $firstValue, $lastValue)
 * @method static Query whereNotBetween($name, $firstValue, $lastValue)
 * @method static Query whereIn($name, array $values)
 * @method static Query whereNotIn($name, array $values)
 * @method static Query whereExists($name, bool $exists = true)
 * @method static Query distance($name, $value, string $distance)
 * @method static Query search(?string $queryString = null, $settings = null, ?int $boost = null)
 * @method static Query nested(string $path)
 * @method static Query highlight(...$args)
 * @method static Query body(array $body = [])
 * @method static Query groupBy(string $field)
 * @method static Query id(?string $id = null)
 * @method static Query skip(int $from = 0)
 * @method static Query take(int $size = 10)
 * @method static Query withGlobalScope(string $identifier, $scope)
 * @method static Query withoutGlobalScope($scope)
 * @method static Query withoutGlobalScopes(array $scopes = null)
 * @method static array removedScopes()
 * @method static bool  hasNamedScope(string $scope)
 * @method static Query scopes($scopes)
 * @method static Query applyScopes()
 * @method static Query remember($ttl, ?string $key = null)
 * @method static Query rememberForever(?string $key = null)
 * @method static Collection get(?string $scrollId = null)
 * @method static Pagination paginate(int $perPage = 10, string $pageName = 'page', ?int $page = null)
 * @method static Model|null first(?string $scrollId = null)
 * @method static Model|mixed|null firstOr(?string $scrollId = null, ?callable $callback = null)
 * @method static Model firstOrFail(?string $scrollId = null)
 * @method static object insert($data, ?string $id = null)
 * @method static object update($data, ?string $id = null)
 * @method static object delete(?string $id = null)
 * @method static int count()
 * @method static object script($script, array $params = [])
 * @method static object increment(string $field, int $count = 1)
 * @method static object decrement(string $field, int $count = 1)
 * @method static array|null performSearch(?string $scrollId = null)
 * @method static ConnectionInterface getConnection()
 * @method static array createIndex(string $name, ?callable $callback = null)
 * @method static array dropIndex(string $name)
 *
 * @package Matchory\Elasticsearch\Facades
 */
class ES extends Facade
{
    /**
     * @inheritDoc
     */
    protected static function getFacadeAccessor(): string
    {
        return ConnectionResolverInterface::class;
    }
}
