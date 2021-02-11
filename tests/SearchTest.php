<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;

use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{

    use ESQueryTrait;

    /**
     * Test the search() method.
     * @return void
     */
    public function testSearchMethod(): void
    {
        self::assertEquals($this->getExpected("foo", 1), $this->getActual("foo", 1));
    }


    /**
     * Get The expected results.
     * @param $q
     * @param $boost
     * @return array
     */
    protected function getExpected($q, $boost = 1)
    {
        $query = $this->getQueryArray();

        $search_params = [];

        $search_params["query"] = $q;

        if($boost > 1){
            $search_params["boost"] = $boost;
        }

        $query["body"]["query"]["bool"]["must"][] = [
            "query_string" => $search_params
        ];

        return $query;
    }


    /**
     * Get The actual results.
     * @param $q
     * @param $boost
     * @return mixed
     */
    protected function getActual($q, $boost = 1)
    {
        return $this->getQueryObject()->search($q, $boost)->query();
    }
}
