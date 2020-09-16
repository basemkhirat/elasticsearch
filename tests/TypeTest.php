<?php

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;

use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{

    use ESQueryTrait;

    /**
     * Test the type() method.
     * @return void
     */
    public function testTypeMethod(): void
    {
        self::assertEquals($this->getExpected("type_1"), $this->getActual("type_1"));
    }


    /**
     * Get The expected results.
     * @param $type
     * @return array
     */
    protected function getExpected($type)
    {
        $query = $this->getQueryArray();

        $query["type"] = $type;

        return $query;
    }


    /**
     * Get The actual results.
     * @param $type
     * @return mixed
     */
    protected function getActual($type)
    {
        return $this->getQueryObject()->type($type)->query();
    }
}
