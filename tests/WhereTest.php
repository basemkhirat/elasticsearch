<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;
use PHPUnit\Framework\TestCase;

class WhereTest extends TestCase
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
        "exists",
        "regexp",
        "contains",
        "notContains",
        "in",
        "notIn",
        "startsWith",
        "endsWith",
        "between",
        "notBetween"
    ];

    /**
     * Test the where() method.
     * @return void
     */
    public function testWhereMethod()
    {
        $this->assertEquals(
            $this->getExpected("id", "=", 1),
            $this->getActual("id", "=", 1)
        );

        $this->assertEquals(
            $this->getExpected("price", "between", [10, 100]),
            $this->getActual("price", "between", [10, 100])
        );

        $this->assertEquals(
            $this->getExpected("age", "notBetween", [18, 25]),
            $this->getActual("age", "notBetween", [18, 25])
        );

        $this->assertEquals(
            $this->getExpected("title", "startsWith", "How to"),
            $this->getActual("title", "startsWith", "How to")
        );

        $this->assertEquals(
            $this->getExpected("filename", "endsWith", ".pdf"),
            $this->getActual("filename", "endsWith", ".pdf")
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

        if ($operator == "regexp") {
            $must[] = ["regexp" => [$name => ["value" => $value]]];
        }

        if ($operator == "contains") {
            $must[] = ["match_phrase" => [$name => $value]];
        }

        if ($operator == "notContains") {
            $must_not[] = ["match_phrase" => [$name => $value]];
        }

        if ($operator == "startsWith") {
            $must[] = ["prefix" => [$name => $value]];
        }

        if ($operator == "endsWith") {
            $must[] = ["wildcard" => [$name => "*" . $value]];
        }

        if ($operator == "between") {
            if (!is_array($value) || count($value) !== 2) {
                throw new \InvalidArgumentException("Between operator requires an array with exactly 2 values");
            }
            $must[] = ["range" => [$name => [
                "gte" => $value[0],
                "lte" => $value[1]
            ]]];
        }

        if ($operator == "notBetween") {
            if (!is_array($value) || count($value) !== 2) {
                throw new \InvalidArgumentException("NotBetween operator requires an array with exactly 2 values");
            }
            $must_not[] = ["range" => [$name => [
                "gte" => $value[0],
                "lte" => $value[1]
            ]]];
        }

        if ($operator == "in") {
            if (!is_array($value)) {
                $value = [$value];
            }
            $must[] = ["terms" => [$name => $value]];
        }

        if ($operator == "notIn") {
            if (!is_array($value)) {
                $value = [$value];
            }
            $must_not[] = ["terms" => [$name => $value]];
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
