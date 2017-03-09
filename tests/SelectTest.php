<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;

class SelectTest extends \PHPUnit_Framework_TestCase
{

    use ESQueryTrait;

    /**
     * Test the select() method.
     * @return void
     */
    public function testIgnoreMethod()
    {
        $this->assertEquals(
            $this->getExpected("title", "content"),
            $this->getActual("title", "content")
        );
    }

    /**
     * Get The expected results.
     * @return array
     */
    protected function getExpected()
    {
        $query = $this->getQueryArray();

        $query["body"]["_source"] = func_get_args();

        return $query;
    }

    /**
     * Get The actual results.
     * @return mixed
     */
    protected function getActual()
    {
        return $this->getQueryObject()->select(func_get_args())->query();
    }
}
