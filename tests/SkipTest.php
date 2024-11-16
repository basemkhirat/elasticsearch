<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;
use PHPUnit\Framework\TestCase;

class SkipTest extends TestCase
{

    use ESQueryTrait;

    /**
     * Test the skip() method.
     * @return void
     */
    public function testSkipMethod()
    {
        $this->assertEquals($this->getExpected(10), $this->getActual(10));
    }

    /**
     * Get The expected results.
     * @return array
     */
    protected function getExpected($from)
    {
        $query = $this->getQueryArray();

        $query["from"] = $from;

        return $query;
    }

    /**
     * Get The actual results.
     * @return mixed
     */
    protected function getActual($from)
    {
        return $this->getQueryObject()->skip($from)->query();
    }
}
