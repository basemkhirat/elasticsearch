<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;

class WhereTest extends \PHPUnit_Framework_TestCase
{

    use ESQueryTrait;

    /**
     * Filter operators
     * @var array
     */
    protected $operators = [
        "=",
        "!=",
        ">",
        ">=",
        "<",
        "<=",
        "like",
        "exists"
    ];

    /**
     * Test the where() method.
     * @return void
     */
    public function testWhereMethod()
    {

        $this->assertEquals(
            $this->getExpected("status", "published"),
            $this->getActual("status", "published")
        );

        $this->assertEquals(
            $this->getExpected("status", "=", "published"),
            $this->getActual("status", "=", "published")
        );

        $this->assertEquals(
            $this->getExpected("views", ">", 1000),
            $this->getActual("views", ">", 1000)
        );

        $this->assertEquals(
            $this->getExpected("views", ">=", 1000),
            $this->getActual("views", ">=", 1000)
        );

        $this->assertEquals(
            $this->getExpected("views", "<=", 1000),
            $this->getActual("views", "<=", 1000)
        );

        $this->assertEquals(
            $this->getExpected("content", "like", "hello"),
            $this->getActual("content", "like", "hello")
        );

        $this->assertEquals(
            $this->getExpected("website", "exists", true),
            $this->getActual("website", "exists", true)
        );

        $this->assertEquals(
            $this->getExpected("website", "exists", false),
            $this->getActual("website", "exists", false)
        );

    }


    /**
     * Get The expected results.
     * @param $name
     * @param string $operator
     * @param null $value
     * @return array
     */
    protected function getExpected($name, $operator = "=", $value = NULL)
    {
        $query = $this->getQueryArray();

        if (!in_array($operator, $this->operators)) {
            $value = $operator;
            $operator = "=";
        }

        $filter = [];
        $must = [];
        $must_not = [];

        if ($operator == "=") {
            $filter[] = ["term" => [$name => $value]];
        }

        if ($operator == ">") {
            $filter[] = ["range" => [$name => ["gt" => $value]]];
        }

        if ($operator == ">=") {
            $filter[] = ["range" => [$name => ["gte" => $value]]];
        }

        if ($operator == "<") {
            $filter[] = ["range" => [$name => ["lt" => $value]]];
        }

        if ($operator == "<=") {
            $filter[] = ["range" => [$name => ["lte" => $value]]];
        }

        if ($operator == "like") {
            $must[] = ["match" => [$name => $value]];
        }

        if ($operator == "exists") {

            if ($value) {
                $must[] = ["exists" => ["field" => $name]];
            } else {
                $must_not[] = ["exists" => ["field" => $name]];
            }

        }

        // Build query body

        $bool = [];

        if (count($must)) {
            $bool["must"] = $must;
        }

        if (count($must_not)) {
            $bool["must_not"] = $must_not;
        }

        if (count($filter)) {
            $bool["filter"] = $filter;
        }


        $query["body"]["query"]["bool"] = $bool;

        return $query;
    }


    /**
     * Get The actual results.
     * @param $name
     * @param string $operator
     * @param null $value
     * @return mixed
     */
    protected function getActual($name, $operator = "=", $value = NULL)
    {
        return $this->getQueryObject()->where($name, $operator, $value)->query();
    }
}
