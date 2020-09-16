<?php

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;

use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{

    use ESQueryTrait;

    /**
     * Test the orderBy() method.
     * @return void
     */
    public function testOrderByMethod(): void
    {
        self::assertEquals($this->getExpected("created_at", "asc"), $this->getActual("created_at", "asc"));
        self::assertEquals($this->getExpected("_score"), $this->getActual("_score"));
    }


    /**
     * Get The expected results.
     * @param $field
     * @param $direction
     * @return array
     */
    protected function getExpected($field, $direction = "desc")
    {
        $query = $this->getQueryArray();

        $query["body"]["sort"][] = [$field => $direction];

        return $query;
    }


    /**
     * Get The actual results.
     * @param $field
     * @param $direction
     * @return mixed
     */
    protected function getActual($field, $direction = "desc")
    {
        return $this->getQueryObject()->orderBy($field, $direction)->query();
    }
}
