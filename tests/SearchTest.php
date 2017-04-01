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

        $search_params = [];

        $search_params["query"] = $q;

        if($boost > 1){
            $search_params["boost"] = $boost;
        }

        $query["body"]["query"]["bool"]["must"][] = [
            "query_string" => $search_params
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
