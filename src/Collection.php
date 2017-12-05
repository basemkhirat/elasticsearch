<?php

namespace Basemkhirat\Elasticsearch;

use Illuminate\Support\Collection as BaseCollection;


class Collection extends BaseCollection
{

    /**
     * Get the collection of items as JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

}
