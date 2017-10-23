<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;

class WhereNotBetweenTest extends \PHPUnit_Framework_TestCase
{

    use ESQueryTrait;

    /**
     * Test the whereNotBetween() method.
     * @return void
     */
    public function testWhereNotBetweenMethod()
    {

        $this->assertEquals(
            $this->getExpected("views", 500, 1000),
            $this->getActual("views", 500, 1000)
        );

        $this->assertEquals(
            $this->getExpected("views", [500, 1000]),
            $this->getActual("views", [500, 1000])
        );

    }


    /**
     * Get The expected results.
     * @param $name
     * @param $first_value
     * @param $second_value
     * @return array
     */
    protected function getExpected($name, $first_value, $second_value = null)
    {
        $query = $this->getQueryArray();

        if (is_array($first_value) && count($first_value) == 2) {
            $second_value = $first_value[1];
            $first_value = $first_value[0];
        }

        $query["body"]["query"]["bool"]["must_not"][] = ["range" => [$name => ["gte" => $first_value, "lte" => $second_value]]];

        return $query;
    }


    /**
     * Get The actual results.
     * @param $name
     * @param $first_value
     * @param $second_value
     * @return mixed
     */
    protected function getActual($name, $first_value, $second_value = null)
    {
        return $this->getQueryObject()->whereNotBetween($name, $first_value, $second_value)->query();
    }
}
