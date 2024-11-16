<?php

namespace Basemkhirat\Elasticsearch;

use Illuminate\Support\Collection as BaseCollection;


class Collection extends BaseCollection
{
    /**
     * Total number of hits
     * @var int
     */
    public $total;

    /**
     * Maximum score of the results
     * @var float|null
     */
    public $max_score;

    /**
     * Time taken for the query in milliseconds
     * @var int
     */
    public $took;

    /**
     * Whether the query timed out
     * @var bool
     */
    public $timed_out;

    /**
     * Scroll ID for pagination
     * @var string|null
     */
    public $scroll_id;

    /**
     * Shard information
     * @var object
     */
    public $shards;

    /**
     * Aggregation results
     * @var array
     */
    public $aggs = [];

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
