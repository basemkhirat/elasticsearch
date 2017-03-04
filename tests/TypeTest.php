<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;

class TypeTest extends \PHPUnit_Framework_TestCase
{

    use ESQueryTrait;

    /**
     * Test the type() method.
     * @return void
     */
    public function testTypeMethod()
    {
        $this->assertEquals($this->getExpected("type_1"), $this->getActual("type_1"));
    }


    /**
     * Get The expected results.
     * @param $type
     * @return array
     */
    protected function getExpected($type)
    {
        $query = $this->getQueryArray();

        $query["type"] = $type;

        return $query;
    }


    /**
     * Get The actual results.
     * @param $type
     * @return mixed
     */
    protected function getActual($type)
    {
        return $this->getQueryObject()->type($type)->query();
    }
}
