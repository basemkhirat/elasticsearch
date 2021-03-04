<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch\Interfaces;

interface CastableInterface
{
    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return mixed
     */
    public static function castUsing(array $arguments);
}
