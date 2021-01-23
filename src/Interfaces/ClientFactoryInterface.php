<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch\Interfaces;

use Elasticsearch\Client;
use Psr\Log\LoggerInterface;

interface ClientFactoryInterface
{
    /**
     * Creates a new client
     *
     * @param array                $hosts
     * @param LoggerInterface|null $logger
     * @param callable|null        $handler
     *
     * @return Client
     */
    public function createClient(
        array $hosts,
        ?LoggerInterface $logger = null,
        ?callable $handler = null
    ): Client;
}
