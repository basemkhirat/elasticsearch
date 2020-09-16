<?php

namespace Matchory\Elasticsearch\Facades;

use Illuminate\Support\Facades\Facade;

class ES extends Facade
{
    /**
     * @inheritDoc
     */
    protected static function getFacadeAccessor(): string
    {
        return 'es';
    }
}
