<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch\Concerns;

use Closure;
use InvalidArgumentException;
use Matchory\Elasticsearch\Interfaces\ScopeInterface;

use function get_class;
use function is_null;
use function is_string;
use function spl_object_hash;

/**
 * Has Global Scopes Trait
 * =======================
 * Adds the ability to use global Elasticsearch query scopes. This concern trait
 * is built similar to the Eloquent trait, but makes use of the Elasticsearch
 * query builder.
 *
 * @package Matchory\Elasticsearch\Concerns
 * @see     \Illuminate\Database\Eloquent\Concerns\HasGlobalScopes
 */
trait HasGlobalScopes
{
    /**
     * @var array<class-string, array<string, Closure|ScopeInterface>>
     */
    protected static $globalScopes = [];

    /**
     * Register a new global scope on the model.
     *
     * @param ScopeInterface|Closure|string $scope
     * @param Closure|null                  $implementation
     *
     * @return Closure|ScopeInterface
     *
     * @throws InvalidArgumentException
     */
    public static function addGlobalScope(
        $scope,
        ?Closure $implementation = null
    ) {
        if (is_string($scope) && ! is_null($implementation)) {
            return static::$globalScopes[static::class][$scope] = $implementation;
        }

        if ($scope instanceof Closure) {
            return static::$globalScopes[static::class][spl_object_hash($scope)] = $scope;
        }

        if ($scope instanceof ScopeInterface) {
            return static::$globalScopes[static::class][get_class($scope)] = $scope;
        }

        throw new InvalidArgumentException(
            'Global scopes must be callable or implement ScopeInterface'
        );
    }

    /**
     * Determine if a model has a global scope.
     *
     * @param ScopeInterface|string $scope
     *
     * @return bool
     */
    public static function hasGlobalScope($scope): bool
    {
        return (bool)static::getGlobalScope($scope);
    }

    /**
     * Get a global scope registered with the model.
     *
     * @param ScopeInterface|string $scope
     *
     * @return ScopeInterface|Closure|null
     */
    public static function getGlobalScope($scope)
    {
        if (is_string($scope)) {
            return static::$globalScopes[static::class][$scope] ?? null;
        }

        return static::$globalScopes[static::class][get_class($scope)] ?? null;
    }

    /**
     * Get the global scopes for this class instance.
     *
     * @return array<string, Closure|ScopeInterface>
     */
    public function getGlobalScopes(): array
    {
        return static::$globalScopes[static::class] ?? [];
    }
}
