<?php

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;

use PHPUnit\Framework\TestCase;

class IgnoreTest extends TestCase
{

    use ESQueryTrait;

    /**
     * Test the ignore() method.
     * @return void
     */
    public function testIgnoreMethod(): void
    {
        self::assertEquals($this->getExpected(404), $this->getActual(404));
        self::assertEquals($this->getExpected(500, 404), $this->getActual(500, 404));
    }

    /**
     * Get The expected results.
     * @return array
     */
    protected function getExpected()
    {
        $query = $this->getQueryArray();

        $query["client"]["ignore"] = func_get_args();

        return $query;
    }

    /**
     * Get The actual results.
     * @return mixed
     */
    protected function getActual()
    {
        return $this->getQueryObject()->ignore(func_get_args())->query();
    }
}
