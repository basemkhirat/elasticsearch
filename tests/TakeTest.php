<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;

class TakeTest extends \PHPUnit_Framework_TestCase
{

    use ESQueryTrait;

    /**
     * Test the take() method.
     * @return void
     */
    public function testTakeMethod()
    {
        $this->assertEquals($this->getExpected(15), $this->getActual(15));
    }


    /**
     * Get The expected results.
     * @param $take
     * @return array
     */
    protected function getExpected($take)
    {
        $query = $this->getQueryArray();

        $query["size"] = $take;

        return $query;
    }


    /**
     * Get The actual results.
     * @param $take
     * @return mixed
     */
    protected function getActual($take)
    {
        return $this->getQueryObject()->take($take)->query();
    }
}
