<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;

class WhereInTest extends \PHPUnit_Framework_TestCase
{

    use ESQueryTrait;

    /**
     * Test the whereIn() method.
     * @return void
     */
    public function testWhereInMethod()
    {

        $this->assertEquals(
            $this->getExpected("status", ["pending", "draft"]),
            $this->getActual("status", ["pending", "draft"])
        );

    }


    /**
     * Get The expected results.
     * @param $name
     * @param array $value
     * @return array
     */
    protected function getExpected($name, $value = [])
    {
        $query = $this->getQueryArray();

        $query["body"]["query"]["bool"]["filter"][] = ["terms" => [$name => $value]];

        return $query;
    }


    /**
     * Get The actual results.
     * @param $name
     * @param array $value
     * @return mixed
     */
    protected function getActual($name, $value = [])
    {
        return $this->getQueryObject()->whereIn($name, $value)->query();
    }
}
