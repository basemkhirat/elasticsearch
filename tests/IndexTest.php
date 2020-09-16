<?php

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;

use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{

    use ESQueryTrait;

    /**
     * Test the index() method.
     * @return void
     */
    public function testIndexMethod(): void
    {
        self::assertEquals($this->getExpected("index_1"), $this->getActual("index_1"));
    }


    /**
     * Get The expected results.
     * @param $index
     * @return array
     */
    protected function getExpected($index)
    {
        $query = $this->getQueryArray();

        $query["index"] = $index;

        return $query;
    }


    /**
     * Get The actual results.
     * @param $index
     * @return mixed
     */
    protected function getActual($index)
    {
        return $this->getQueryObject()->index($index)->query();
    }
}
