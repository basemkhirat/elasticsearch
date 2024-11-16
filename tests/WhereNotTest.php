<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Query;
use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;
use PHPUnit\Framework\TestCase;

class WhereNotTest extends TestCase
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
        "in",
        "between",
        "startsWith",
        "endsWith"
    ];

    /**
     * Test the whereNot() method.
     * @return void
     */
    public function testWhereNotMethod()
    {
        // Test between operator
        $query = new Query("my_index", "my_type");
        $query->whereNot("price", "between", [10, 100]);
        $this->assertArrayHasKey("must_not", $query->query()["body"]["query"]["bool"]);
        $this->assertEquals(
            ["range" => ["price" => ["gte" => 10, "lte" => 100]]],
            $query->query()["body"]["query"]["bool"]["must_not"][0]
        );

        // Test startsWith operator
        $query = new Query("my_index", "my_type");
        $query->whereNot("filename", "startsWith", "temp_");
        $this->assertArrayHasKey("must_not", $query->query()["body"]["query"]["bool"]);
        $this->assertEquals(
            ["prefix" => ["filename" => "temp_"]],
            $query->query()["body"]["query"]["bool"]["must_not"][0]
        );

        // Test endsWith operator
        $query = new Query("my_index", "my_type");
        $query->whereNot("filename", "endsWith", ".tmp");
        $this->assertArrayHasKey("must_not", $query->query()["body"]["query"]["bool"]);
        $this->assertEquals(
            ["wildcard" => ["filename" => "*.tmp"]],
            $query->query()["body"]["query"]["bool"]["must_not"][0]
        );
    }


    /**
     * Get The actual results.
     * @param $name
     * @param string $operator
     * @param null $value
     * @return array
     */
    protected function getActual($name, $operator = "=", $value = NULL)
    {
        $query = new Query("my_index", "my_type");
        $query->whereNot($name, $operator, $value);
        $result = $query->query();
        return $result["body"];
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

        if ($operator == "regexp") {
            $must_not[] = ["regexp" => [$name => ["value" => $value]]];
        }

        if ($operator == "contains") {
            $must_not[] = ["match_phrase" => [$name => $value]];
        }

        if ($operator == "in") {
            if (!is_array($value)) {
                $value = [$value];
            }
            $must_not[] = ["terms" => [$name => $value]];
        }

        if ($operator == "endsWith") {
            $must_not[] = ["wildcard" => [$name => "*" . $value]];
        }

        if ($operator == "between") {
            if (!is_array($value) || count($value) !== 2) {
                throw new \InvalidArgumentException("Between operator requires an array with exactly 2 values");
            }
            $must_not[] = ["range" => [$name => [
                "gte" => $value[0],
                "lte" => $value[1]
            ]]];
        }

        if ($operator == "startsWith") {
            $must_not[] = ["prefix" => [$name => $value]];
        }

        // Build query body

        $bool = [];

        if (count($must)) {
            $bool["must"] = $must;
        }

        if (count($must_not)) {
            $bool["must_not"] = $must_not;
        }

        return [
            "_source" => [
                "include" => [],
                "exclude" => []
            ],
            "query" => [
                "bool" => $bool
            ]
        ];
    }
}
