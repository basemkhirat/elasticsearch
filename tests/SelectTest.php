<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{
    use ESQueryTrait;

    protected function setUp(): void
    {
        parent::setUp();
        // Don't select '*' by default for this test
        $this->defaultSelect = false;
    }

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
        $query["body"]["_source"] = [
            "include" => func_get_args(),
            "exclude" => []
        ];
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
