<?php

namespace Matchory\Elasticsearch\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class ES
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
        return 'es';
    }
}
