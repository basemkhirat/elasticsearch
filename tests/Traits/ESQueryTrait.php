<?php

namespace Matchory\Elasticsearch\Tests\Traits;

use Matchory\Elasticsearch\Query;

/**
 * Class ESQueryTrait
 */
trait ESQueryTrait
{

    /**
     * Test index name
     *
     * @var string
     */
    protected $index = "my_index";

    /**
     * Test type name
     *
     * @var string
     */
    protected $type = "my_type";

    /**
     * Test query limit
     *
     * @var int
     */
    protected $take = 10;

    /**
     * Test query offset
     *
     * @var int
     */
    protected $skip = 0;

    /**
     * Expected query array
     *
     * @return array
     */
    protected function getQueryArray(): array
    {
        return [
            'index' => $this->index,
            'type' => $this->type,
            'body' => [],
            'from' => $this->skip,
            'size' => $this->take,
        ];
    }

    /**
     * ES query object
     *
     * @return Query
     */
    protected function getQueryObject(): Query
    {
        return (new Query())
            ->index($this->index)
            ->type($this->type)
            ->take($this->take)
            ->skip($this->skip);
    }
}
