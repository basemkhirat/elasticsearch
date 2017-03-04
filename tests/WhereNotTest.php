<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;

class WhereNotTest extends \PHPUnit_Framework_TestCase
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
     * Test the whereNot() method.
     * @return void
     */
    public function testWhereNotMethod()
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

        $must = [];
        $must_not = [];

        if ($operator == "=") {
            $must_not[] = ["term" => [$name => $value]];
        }

        if ($operator == ">") {
            $must_not[] = ["range" => [$name => ["gt" => $value]]];
        }

        if ($operator == ">=") {
            $must_not[] = ["range" => [$name => ["gte" => $value]]];
        }

        if ($operator == "<") {
            $must_not[] = ["range" => [$name => ["lt" => $value]]];
        }

        if ($operator == "<=") {
            $must_not[] = ["range" => [$name => ["lte" => $value]]];
        }

        if ($operator == "like") {
            $must_not[] = ["match" => [$name => $value]];
        }

        if ($operator == "exists") {

            if ($value) {
                $must_not[] = ["exists" => ["field" => $name]];
            } else {
                $must[] = ["exists" => ["field" => $name]];
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
        return $this->getQueryObject()->whereNot($name, $operator, $value)->query();
    }
}
