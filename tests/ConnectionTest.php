<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\ElasticsearchServiceProvider;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;
use Matchory\Elasticsearch\Tests\Traits\ResolvesConnections;
use Orchestra\Testbench\TestCase;

class ConnectionTest extends TestCase
{
    use ResolvesConnections;

    public function testIndexReturnsNewQueryOnIndex(): void
    {
        /** @var ConnectionInterface $connection */
        $connection = $this->app->make(ConnectionInterface::class);
        $query = $connection->index('foo');

        self::assertSame('foo', $query->getIndex());
    }

    protected function getEnvironmentSetUp($app): void
    {
        $this->registerResolver($app);
    }

    protected function getPackageProviders($app): array
    {
        return [
            ElasticsearchServiceProvider::class,
        ];
    }
}
