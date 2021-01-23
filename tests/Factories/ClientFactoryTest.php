<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Matchory\Elasticsearch\Tests\Factories;

use Elasticsearch\Client;
use Matchory\Elasticsearch\Factories\ClientFactory;
use PHPUnit\Framework\TestCase;

class ClientFactoryTest extends TestCase
{
    public function testCreateClient(): void
    {
        $factory = new ClientFactory();
        $client = $factory->createClient([]);

        self::assertInstanceOf(Client::class, $client);
    }

    public function testCreateClientWithHosts(): void
    {
        $hosts = ['foo', 'bar', 'baz'];
        $factory = new ClientFactory();
        $client = $factory->createClient($hosts);

        self::assertContains(
            $client->transport->getConnection()->getHost(),
            $hosts
        );
    }
}
