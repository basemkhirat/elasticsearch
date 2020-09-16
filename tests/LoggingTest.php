<?php
/** @noinspection PhpUnhandledExceptionInspection */

/**
 * Created by PhpStorm.
 * User: mike
 * Date: 07/05/18
 * Time: 19:58
 */

namespace Matchory\Elasticsearch\Tests;

use Elasticsearch\ClientBuilder;
use Matchory\Elasticsearch\Connection;
use PHPUnit\Framework\TestCase;

class LoggingTest extends TestCase
{

    public function testConfigureLogging(): void
    {
        $client = ClientBuilder::create();
        $newClientBuilder = Connection::configureLogging($client, [
            'logging' => [
                'enabled' => true,
                'level' => 'all',
                'location' => '../src/storage/logs/elasticsearch.log',
            ],
        ]);

        self::assertInstanceOf(
            ClientBuilder::class,
            $newClientBuilder
        );
    }
}
