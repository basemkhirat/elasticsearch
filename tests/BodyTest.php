<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;

class BodyTest extends \PHPUnit_Framework_TestCase
{

    use ESQueryTrait;


    /**
     * Test the body() method.
     * @return void
     */
    public function testBodyMethod()
    {

        $body = [
            "query" => [
                "bool" => [
                    "must" => [
                        ["match" => ["address" => "mill"]],
                    ]
                ]
            ]
        ];

        $this->assertEquals(
            $this->getExpected($body),
            $this->getActual($body)
        );


    }

    /**
     * Get The expected results.
     * @param $body array
     * @return array
     */
    protected function getExpected($body = [])
    {
        $query = $this->getQueryArray();

        $query["body"] = $body;

        return $query;
    }


    /**
     * Get The actual results.
     * @param $body array
     * @return mixed
     */
    protected function getActual($body = [])
    {
        return $this->getQueryObject()->body($body)->query();
    }
}
