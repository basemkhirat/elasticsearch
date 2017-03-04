<?php

namespace Basemkhirat\Elasticsearch\Tests;

use Basemkhirat\Elasticsearch\Tests\Traits\ESQueryTrait;

class DistanceTest extends \PHPUnit_Framework_TestCase
{

    use ESQueryTrait;

    /**
     * Test the distance() method.
     * @return void
     */
    public function testDistanceMethod()
    {
        $this->assertEquals(
            $this->getExpected("location", ['lat' => -33.8688197, 'lon' => 151.20929550000005], "10km"),
            $this->getActual("location", ['lat' => -33.8688197, 'lon' => 151.20929550000005], "10km")
        );

        $this->assertNotEquals(
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
