<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class WhereTest extends TestCase
{
    use ESQueryTrait;

    /**
     * Filter operators
     *
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
    ];

    /**
     * Test the where() method.
     *
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testWhereMethod(): void
    {
        self::assertEquals(
            $this->getExpected("status", "published"),
            $this->getActual("status", "published")
        );

        self::assertEquals(
            $this->getExpected("status", "=", "published"),
            $this->getActual("status", "=", "published")
        );

        self::assertEquals(
            $this->getExpected("views", ">", 1000),
            $this->getActual("views", ">", 1000)
        );

        self::assertEquals(
            $this->getExpected("views", ">=", 1000),
            $this->getActual("views", ">=", 1000)
        );

        self::assertEquals(
            $this->getExpected("views", "<=", 1000),
            $this->getActual("views", "<=", 1000)
        );

        self::assertEquals(
            $this->getExpected("content", "like", "hello"),
            $this->getActual("content", "like", "hello")
        );

        self::assertEquals(
            $this->getExpected("website", "exists", true),
            $this->getActual("website", "exists", true)
        );

        self::assertEquals(
            $this->getExpected("website", "exists", false),
            $this->getActual("website", "exists", false)
        );
    }

    /**
     * Get The expected results.
     *
     * @param string     $name
     * @param string     $operator
     * @param mixed|null $value
     *
     * @return array
     */
    protected function getExpected(
        string $name,
        string $operator = "=",
        $value = null
    ): array {
        $query = $this->getQueryArray();

        if ( ! in_array(
            $operator,
            $this->operators,
            true
        )) {
            $value = $operator;
            $operator = "=";
        }

        $filter = [];
        $must = [];
        $must_not = [];

        if ($operator === "=") {
            $filter[] = ["term" => [$name => $value]];
        }

        if ($operator === ">") {
            $filter[] = ["range" => [$name => ["gt" => $value]]];
        }

        if ($operator === ">=") {
            $filter[] = ["range" => [$name => ["gte" => $value]]];
        }

        if ($operator === "<") {
            $filter[] = ["range" => [$name => ["lt" => $value]]];
        }

        if ($operator === "<=") {
            $filter[] = ["range" => [$name => ["lte" => $value]]];
        }

        if ($operator === "like") {
            $must[] = ["match" => [$name => $value]];
        }

        if ($operator === "exists") {
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
     *
     * @param string     $name
     * @param string     $operator
     * @param mixed|null $value
     *
     * @return array
     */
    protected function getActual(
        string $name,
        string $operator = "=",
        $value = null
    ): array {
        return $this
            ->getQueryObject()
            ->where($name, $operator, $value)
            ->query();
    }
}
