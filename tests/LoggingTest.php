<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 07/05/18
 * Time: 19:58
 */

namespace Basemkhirat\Elasticsearch\Tests;


use Basemkhirat\Elasticsearch\Connection;
use Elasticsearch\ClientBuilder;
use Monolog\Logger;

class LoggingTest extends \PHPUnit_Framework_TestCase
{

    public function testConfigureLogging()
    {
        $client = ClientBuilder::create();
        $newClientBuilder = Connection::configureLogging($client,[
            'logging'=>[
                'enabled'=>true,
                'level'=>'all',
                'location'=>'../src/storage/logs/elasticsearch.log'
            ]
        ]);

        $this->assertInstanceOf(ClientBuilder::class,$newClientBuilder);
        $this->assertAttributeInstanceOf(Logger::class,'logger',$newClientBuilder);
    }
}