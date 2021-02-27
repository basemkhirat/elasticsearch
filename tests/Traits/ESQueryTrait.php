<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace Matchory\Elasticsearch\Tests\Traits;

use Elasticsearch\Client;
use Matchory\Elasticsearch\Connection;
use Matchory\Elasticsearch\Query;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\ClassAlreadyExistsException;
use PHPUnit\Framework\MockObject\ClassIsFinalException;
use PHPUnit\Framework\MockObject\DuplicateMethodException;
use PHPUnit\Framework\MockObject\InvalidMethodNameException;
use PHPUnit\Framework\MockObject\OriginalConstructorInvocationRequiredException;
use PHPUnit\Framework\MockObject\ReflectionException;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\MockObject\UnknownTypeException;

/**
 * Class ESQueryTrait
 */
trait ESQueryTrait
{
    /**
     * Test index name
     *
     * @var string
     */
    protected $index = "my_index";

    /**
     * Test type name
     *
     * @var string
     */
    protected $type = "my_type";

    /**
     * Test query limit
     *
     * @var int
     */
    protected $take = 10;

    /**
     * Test query offset
     *
     * @var int
     */
    protected $skip = 0;

    /**
     * Expected query array
     *
     * @param array $body
     *
     * @return array
     */
    protected function getQueryArray(array $body = []): array
    {
        return [
            'index' => $this->index,
            'type' => $this->type,
            'body' => $body,
            'from' => $this->skip,
            'size' => $this->take,
        ];
    }

    /**
     * ES query object
     *
     * @return Query
     * @throws InvalidArgumentException
     * @throws ClassAlreadyExistsException
     * @throws ClassIsFinalException
     * @throws DuplicateMethodException
     * @throws InvalidMethodNameException
     * @throws OriginalConstructorInvocationRequiredException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws UnknownTypeException
     */
    protected function getQueryObject(): Query
    {
        return (new Query(
            new Connection(
                $this->getMockBuilder(Client::class)
                     ->disableOriginalConstructor()
                     ->getMock()
            )
        ))
            ->index($this->index)
            ->type($this->type)
            ->take($this->take)
            ->skip($this->skip);
    }
}
