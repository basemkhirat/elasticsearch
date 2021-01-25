<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch\Interfaces;

use Matchory\Elasticsearch\Model;
use Matchory\Elasticsearch\Query;

interface ScopeInterface
{
    /**
     * Apply the scope to a given Elasticsearch query builder.
     *
     * @param Query $query
     * @param Model $model
     *
     * @return void
     */
    public function apply(Query $query, Model $model): void;
}
