<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch\Interfaces;

use Matchory\Elasticsearch\Query;

interface ConnectionInterface
{
    /**
     * Creates a new Elasticsearch query
     *
     * @return Query
     */
    public function newQuery(): Query;
}
