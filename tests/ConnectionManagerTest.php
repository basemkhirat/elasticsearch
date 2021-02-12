<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Matchory\Elasticsearch\Tests;

use Elasticsearch\Client;
use Matchory\Elasticsearch\Connection;
use Matchory\Elasticsearch\ConnectionManager;
use Matchory\Elasticsearch\Factories\ClientFactory;
use Matchory\Elasticsearch\Interfaces\ConnectionInterface;
use Matchory\Elasticsearch\Interfaces\ConnectionResolverInterface;
use Mockery\Mock;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class ConnectionManagerTest extends TestCase
{
    public function testCreatesInstance(): void
    {
        $instance = new ConnectionManager(
            [],
            new ClientFactory()
        );

        self::assertInstanceOf(ConnectionManager::class, $instance);
        self::assertInstanceOf(ConnectionResolverInterface::class, $instance);
    }

    public function testSetsDefaultConnection(): void
    {
        $instance = new ConnectionManager(
            [],
            new ClientFactory()
        );

        self::assertEmpty($instance->getDefaultConnection());

        $instance->setDefaultConnection('foo');

        self::assertSame('foo', $instance->getDefaultConnection());
    }

    /**
     *
     * @noinspection PhpParamsInspection
     */
    public function testResolvesDefaultConnectionIfNameNotSpecified(): void
    {
        $instance = new ConnectionManager(
            [],
            new ClientFactory()
        );
        $connection = $this->mock(ConnectionInterface::class);
        $instance->addConnection('', $connection);

        self::assertSame($connection, $instance->connection());
    }

    /**
     *
     * @noinspection PhpParamsInspection
     */
    public function testResolvesConnectionsByName(): void
    {
        $manager = new ConnectionManager(
            [],
            new ClientFactory()
        );
        $c1 = $this->mock(ConnectionInterface::class);
        $c2 = $this->mock(ConnectionInterface::class);

        $manager->addConnection('foo', $c1);
        $manager->addConnection('bar', $c2);

        self::assertSame($c1, $manager->connection('foo'));
        self::assertSame($c2, $manager->connection('bar'));
    }

    public function testCreatesConnectionsWithCacheInstance(): void
    {
        /** @var CacheInterface&Mock $cache */
        $cache = $this->mock(CacheInterface::class);
        $instance = new ConnectionManager(
            [
                'connections' => [
                    'foo' => [
                        'servers' => [
                            '0.0.0.0',
                        ],
                    ],
                ],
            ],
            new ClientFactory(),
            $cache
        );

        $connection = $instance->connection('foo');
        self::assertSame($cache, $connection->getCache());
    }

    public function testCreatesConnectionsWithLoggerInstance(): void
    {
        /** @var LoggerInterface&Mock $logger */
        $logger = $this->mock(LoggerInterface::class);
        $callback = static function ($clientBuilderLogger) use ($logger) {
            self::assertSame($logger, $clientBuilderLogger);
        };
        $clientFactory = new class($callback) extends ClientFactory {
            protected $callback;

            public function __construct(callable $callback)
            {
                $this->callback = $callback;
            }

            public function createClient(
                array $hosts,
                ?LoggerInterface $logger = null,
                ?callable $handler = null
            ): Client {
                $callback = $this->callback;
                $callback($logger);

                return parent::createClient($hosts);
            }
        };
        $instance = new ConnectionManager(
            [
                'connections' => [
                    'foo' => [
                        'servers' => [
                            '0.0.0.0',
                        ],
                    ],
                ],
            ],
            $clientFactory,
            null,
            $logger
        );
        $instance->connection('foo');
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @noinspection PhpParamsInspection
     */
    public function testAddsConnection(): void
    {
        $instance = new ConnectionManager(
            [],
            new ClientFactory()
        );

        self::assertFalse($instance->hasConnection('foo'));

        $instance->addConnection('foo', $this->mock(
            ConnectionInterface::class
        ));

        self::assertTrue($instance->hasConnection('foo'));
    }

    /** @noinspection PhpUndefinedMethodInspection
     * @noinspection PhpUnusedParameterInspection
     */
    public function testProxiesCallsToDefaultConnection(): void
    {
        $manager = new ConnectionManager(
            [],
            new ClientFactory()
        );

        $expected = 42;
        $connection = $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connection
            ->expects(self::any())
            ->method('__call')
            ->with('test')
            ->willReturnCallback(function ($method, $args) use ($expected) {
                self::assertSame($expected, $args[0]);
            });

        $manager->addConnection('', $connection);
        $manager->test($expected);
    }
}
