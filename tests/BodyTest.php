<?php

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\ClassAlreadyExistsException;
use PHPUnit\Framework\MockObject\ClassIsFinalException;
use PHPUnit\Framework\MockObject\DuplicateMethodException;
use PHPUnit\Framework\MockObject\InvalidMethodNameException;
use PHPUnit\Framework\MockObject\OriginalConstructorInvocationRequiredException;
use PHPUnit\Framework\MockObject\ReflectionException;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\MockObject\UnknownTypeException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class BodyTest extends TestCase
{
    use ESQueryTrait;

    /**
     * Test the body() method.
     *
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testBodyMethod(): void
    {
        $body = [
            "query" => [
                "bool" => [
                    "must" => [
                        ["match" => ["address" => "mill"]],
                    ],
                ],
            ],
        ];

        self::assertEquals(
            $this->getExpected($body),
            $this->getActual($body)
        );
    }

    /**
     * Get the expected results.
     *
     * @param $body array
     *
     * @return array
     */
    protected function getExpected(array $body = []): array
    {
        return $this->getQueryArray($body);
    }

    /**
     * Get the actual results.
     *
     * @param $body array
     *
     * @return array
     * @throws \PHPUnit\Framework\InvalidArgumentException
     * @throws ClassAlreadyExistsException
     * @throws ClassIsFinalException
     * @throws DuplicateMethodException
     * @throws InvalidMethodNameException
     * @throws OriginalConstructorInvocationRequiredException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws UnknownTypeException
     */
    protected function getActual(array $body = []): array
    {
        return $this
            ->getQueryObject()
            ->body($body)
            ->toArray();
    }
}
