<?php

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;

use PHPUnit\Framework\TestCase;

class WhereNotBetweenTest extends TestCase
{

    use ESQueryTrait;

    /**
     * Test the whereNotBetween() method.
     * @return void
     */
    public function testWhereNotBetweenMethod(): void
    {

        self::assertEquals(
            $this->getExpected("views", 500, 1000),
            $this->getActual("views", 500, 1000)
        );

        self::assertEquals(
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
