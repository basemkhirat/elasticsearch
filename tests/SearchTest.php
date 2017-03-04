<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;

class SearchTest extends \PHPUnit_Framework_TestCase
{

    use ESQueryTrait;

    /**
     * Test the search() method.
     * @return void
     */
    public function testSearchMethod()
    {
        $this->assertEquals($this->getExpected("foo", 1), $this->getActual("foo", 1));
    }


    /**
     * Get The expected results.
     * @param $q
     * @param $boost
     * @return array
     */
    protected function getExpected($q, $boost = 1)
    {
        $query = $this->getQueryArray();

        $query["body"]["query"]["bool"]["must"][] = [
            "query_string" => [
                "query" => $q,
                "boost" => $boost
            ]
        ];

        return $query;
    }


    /**
     * Get The actual results.
     * @param $q
     * @param $boost
     * @return mixed
     */
    protected function getActual($q, $boost = 1)
    {
        return $this->getQueryObject()->search($q, $boost)->query();
    }
}
