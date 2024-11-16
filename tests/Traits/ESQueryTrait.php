<?php

namespace Basemkhirat\Elasticsearch\Tests\Traits;

use Basemkhirat\Elasticsearch\Query;

/**
 * Class ESQueryTrait
 */
Trait ESQueryTrait
{
    /**
     * Whether to select '*' by default
     * @var bool
     */
    protected $defaultSelect = true;

    /**
     * Test index name
     * @var string
     */
    protected $index = "my_index";

    /**
     * Test type name
     * @var string
     */
    protected $type = "my_type";

    /**
     * Test query limit
     * @var int
     */
    protected $take = 10;

    /**
     * Test query offset
     * @var int
     */
    protected $skip = 0;

    /**
     * Expected query array
     * @return array
     */
    protected function getQueryArray()
    {
        $query = [
            'index' => $this->index,
            'type' => $this->type,
            'body' => [],
            'size' => $this->take
        ];

        if ($this->defaultSelect) {
            $query['body']['_source'] = [
                'include' => ['*'],
                'exclude' => []
            ];
        }

        return $query;
    }

    /**
     * ES query object
     * @return Query
     */
    protected function getQueryObject()
    {
        $query = (new Query())
            ->index($this->index)
            ->type($this->type)
            ->take($this->take)
            ->skip($this->skip);

        if ($this->defaultSelect) {
            $query->select('*');
        }

        return $query;
    }
}
