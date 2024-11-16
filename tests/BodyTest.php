<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Query;
use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;
use PHPUnit\Framework\TestCase;

class BodyTest extends TestCase
{
    use ESQueryTrait;

    protected Query $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = $this->getQueryObject();
    }

    /**
     * Test the body() method.
     * @return void
     */
    public function testBodyMethod()
    {
        $body = [
            "query" => [
                "match_all" => new \stdClass()
            ]
        ];

        $query = $this->query->body($body);

        $expected = [
            "index" => "my_index",
            "type" => "my_type",
            "body" => [
                "query" => [
                    "match_all" => new \stdClass()
                ],
                "_source" => [
                    "include" => ["*"],
                    "exclude" => []
                ]
            ],
            "size" => 10
        ];

        $this->assertEquals($expected, $query->query());
    }
}
