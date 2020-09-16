<?php

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;

use PHPUnit\Framework\TestCase;

class DistanceTest extends TestCase
{

    use ESQueryTrait;

    /**
     * Test the distance() method.
     * @return void
     */
    public function testDistanceMethod(): void
    {
        self::assertEquals(
            $this->getExpected("location", ['lat' => -33.8688197, 'lon' => 151.20929550000005], "10km"),
            $this->getActual("location", ['lat' => -33.8688197, 'lon' => 151.20929550000005], "10km")
        );

        self::assertNotEquals(
            $this->getExpected("location", ['lat' => -33.8688197, 'lon' => 151.20929550000005], "10km"),
            $this->getActual("location", ['lat' => -33.8688197, 'lon' => 151.20929550000005], "15km")
        );
    }


    /**
     * Get The expected results.
     * @param $field
     * @param $geo_point
     * @param $distance
     * @return array
     */
    protected function getExpected($field, $geo_point, $distance)
    {
        $query = $this->getQueryArray();

        $query['body']['query']['bool']['filter'][] = [
            'geo_distance' => [
                $field => $geo_point,
                'distance' => $distance
            ]
        ];

        return $query;
    }


    /**
     * Get The actual results.
     * @param $field
     * @param $geo_point
     * @param $distance
     * @return mixed
     */
    protected function getActual($field, $geo_point, $distance)
    {
        return $this->getQueryObject()->distance($field, $geo_point, $distance)->query();
    }
}
