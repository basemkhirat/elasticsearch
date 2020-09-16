<?php

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;
use PHPUnit\Framework\ExpectationFailedException;
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
        $query = $this->getQueryArray();

        $query["body"] = $body;

        if ( ! isset($body['_source'])) {
            $body['_source'] = [
                'include' => [],
                'exclude' => [],
            ];
        }

        return $query;
    }

    /**
     * Get the actual results.
     *
     * @param $body array
     *
     * @return array
     */
    protected function getActual(array $body = []): array
    {
        return $this
            ->getQueryObject()
            ->body($body)
            ->query();
    }
}
