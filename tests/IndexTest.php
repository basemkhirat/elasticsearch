<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;

class IndexTest extends \PHPUnit_Framework_TestCase
{

    use ESQueryTrait;

    /**
     * Test the index() method.
     * @return void
     */
    public function testIndexMethod()
    {
        $this->assertEquals($this->getExpected("index_1"), $this->getActual("index_1"));
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
