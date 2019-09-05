<?php
/**
 * Created by PhpStorm.
 * User: carlos
 * Date: 06/02/19
 * Time: 17:08
 */

namespace CarlosOCarvalho\Elasticsearch\Tests;


use CarlosOCarvalho\Elasticsearch\Tests\Traits\ESQueryTrait;

class HighlightTest  extends \PHPUnit_Framework_TestCase
{

    use ESQueryTrait;


    /**
     * Test highlight use all params
     * @return void
     */
    public  function testHighlightMethodByAllParameters()
    {
         $highlight = [
             'fields' => [
                  'desc' => [
                      "number_of_fragments" => 0,
                      'pre_tags' => ['<span>'], 'post_tags' => ['</span>']]
             ]
         ];
         $this->assertEquals($this->getExpected($highlight), $this->getActual('desc'));




    }

    /**
     *
     * Test use by array
     * @return void
     */
    public  function testHighlightMethodByArray()
    {
        $highlight = [
            'fields' => [
                'desc' => [
                    "number_of_fragments" => 0,
                    'pre_tags' => ['<span class="test-highlight">'], 'post_tags' => ['</span>']]
            ]
        ];
        $this->assertEquals($this->getExpected($highlight), $this->getActual(['desc' => $highlight['fields']['desc']]));




    }
    /**
     * Get The expected results.
     * @param $highlight array
     * @return array
     */
    protected function getExpected($highlight = [])
    {
        $query = $this->getQueryArray();

        $query['body']["highlight"] = $highlight;

        return $query;
    }


    /**
     * Get The actual results.
     * @param $highlight array
     * @return mixed
     */
    protected function getActual($name, $pre_tag = ['<span>'], $post_tag = ['</span>'])
    {
        return $this->getQueryObject()->highlight($name, $pre_tag, $post_tag)->query();
    }

}