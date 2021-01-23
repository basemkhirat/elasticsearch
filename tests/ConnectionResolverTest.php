<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Connection;
use Matchory\Elasticsearch\ConnectionResolver;
use Matchory\Elasticsearch\Factories\ClientFactory;
use PHPUnit\Framework\TestCase;

class ConnectionResolverTest extends TestCase
{
    public function testAddConnection(): void
    {
        $resolver = new ConnectionResolver();
        $clientFactory = new ClientFactory();
        $client = $clientFactory->createClient([]);
        $connection = new Connection($client);

        self::assertFalse($resolver->hasConnection('foo'));

        $resolver->addConnection('foo', $connection);

        self::assertTrue($resolver->hasConnection('foo'));
    }

    public function testConnection(): void
    {
        $resolver = new ConnectionResolver();
        $clientFactory = new ClientFactory();
        $client = $clientFactory->createClient([]);
        $connection = new Connection($client);

        self::assertFalse($resolver->hasConnection('foo'));

        $resolver->addConnection('foo', $connection);

        self::assertTrue($resolver->hasConnection('foo'));
        self::assertSame(
            $connection,
            $resolver->connection('foo')
        );
    }

    public function testSetDefaultConnection(): void
    {
        $resolver = new ConnectionResolver();
        $clientFactory = new ClientFactory();
        $client = $clientFactory->createClient([]);
        $connection = new Connection($client);

        self::assertFalse($resolver->hasConnection('foo'));

        $resolver->addConnection('foo', $connection);
        $resolver->setDefaultConnection('foo');

        self::assertSame('foo', $resolver->getDefaultConnection());
        self::assertTrue($resolver->hasConnection('foo'));
        self::assertSame($connection, $resolver->connection());
    }
}
