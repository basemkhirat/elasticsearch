<?php

use Basemkhirat\Elasticsearch\Query;

class ESQueryTest extends PHPUnit_Framework_TestCase
{
    protected $connection = 'default';
    protected $index = 'index1';
    protected $type = 'type1';

    /**
     * Returns array with default query.
     * It can be used for testing of any filtering function. Just add what you need for each particular method.
     *
     * @return array
     */
    protected function defaultQueryTemplate() {
        return [
            'index' => $this->index,
            'type' => $this->type,
            'body' => [
                '_source' => [],
                'query' => [
                    'bool' => [
                        'must' => [],
                        'must_not' => [],
                        'filter' => []
                    ]
                ],
                'sort' => []
            ],
            'from' => 0,
            'size' => 10,
            'client' => [
                'ignore' => []
            ]
        ];
    }

    public function testQueryForDistanceFilter()
    {
        $geo_point = ['lat' => -33.8688197, 'lon' => 151.20929550000005];
        $distance = '10km';

        // Test on the same parameters.

        $expected_query = $this->prepareExpectedQueryWithDistanceFilter($geo_point, $distance);

        $query = new Query(null);

        $actual_query = $query->index($this->index)
            ->type($this->type)
            ->distance('location', $geo_point, $distance)
            ->query();

        $this->assertEquals($expected_query, $actual_query);

        // Test on different parameters.

        // We intentionally change distance parameter that we pass to distance() method
        // to cause actual results to differ from the expected results.
        $distance = '20km';

        $query = new Query(null);

        $actual_query = $query->index($this->index)
            ->type($this->type)
            ->distance('location', $geo_point, $distance)
            ->query();

        $this->assertNotEquals($expected_query, $actual_query);
    }

    /**
     * Prepare Elastic search query with distance filter.
     *
     * @param $geo_point
     * @param $distance
     * @return array
     */
    private function prepareExpectedQueryWithDistanceFilter($geo_point, $distance) {
        $query = $this->defaultQueryTemplate();

        $query['body']['query']['bool']['filter'][] =
            [
                'geo_distance' => [
                    'location' => $geo_point,
                    'distance' => $distance
                ]
            ];

        return $query;
    }
}
