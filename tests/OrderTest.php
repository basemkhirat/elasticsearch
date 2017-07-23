<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;

class OrderTest extends \PHPUnit_Framework_TestCase
{

    use ESQueryTrait;

    /**
     * Test the orderBy() method.
     * @return void
     */
    public function testOrderByMethod()
    {
        $this->assertEquals($this->getExpected("created_at", "asc"), $this->getActual("created_at", "asc"));
        $this->assertEquals($this->getExpected("_score"), $this->getActual("_score"));
    }


    /**
     * Get The expected results.
     * @param $field
     * @param $direction
     * @return array
     */
    protected function getExpected($field, $direction = "desc")
    {
        $query = $this->getQueryArray();

        $query["body"]["sort"][] = [$field => $direction];

        return $query;
    }


    /**
     * Get The actual results.
     * @param $field
     * @param $direction
     * @return mixed
     */
    protected function getActual($field, $direction = "desc")
    {
        return $this->getQueryObject()->orderBy($field, $direction)->query();
    }
}
