<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;

class IgnoreTest extends \PHPUnit_Framework_TestCase
{

    use ESQueryTrait;

    /**
     * Test the ignore() method.
     * @return void
     */
    public function testIgnoreMethod()
    {
        $this->assertEquals($this->getExpected(404), $this->getActual(404));
        $this->assertEquals($this->getExpected(500, 404), $this->getActual(500, 404));
    }

    /**
     * Get The expected results.
     * @return array
     */
    protected function getExpected()
    {
        $query = $this->getQueryArray();

        $query["client"]["ignore"] = func_get_args();

        return $query;
    }

    /**
     * Get The actual results.
     * @return mixed
     */
    protected function getActual()
    {
        return $this->getQueryObject()->ignore(func_get_args())->query();
    }
}
