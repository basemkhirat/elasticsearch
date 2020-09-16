<?php

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class WhereInTest extends TestCase
{
    use ESQueryTrait;

    /**
     * Test the whereIn() method.
     *
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testWhereInMethod(): void
    {
        self::assertEquals(
            $this->getExpected("status", ["pending", "draft"]),
            $this->getActual("status", ["pending", "draft"])
        );
    }

    /**
     * Get The expected results.
     *
     * @param string $name
     * @param array  $value
     *
     * @return array
     */
    protected function getExpected(string $name, array $value = []): array
    {
        $query = $this->getQueryArray();

        $query["body"]["query"]["bool"]["filter"][] = [
            "terms" => [
                $name => $value,
            ],
        ];

        return $query;
    }

    /**
     * Get The actual results.
     *
     * @param string $name
     * @param array  $value
     *
     * @return array
     */
    protected function getActual($name, $value = []): array
    {
        return $this
            ->getQueryObject()
            ->whereIn($name, $value)
            ->query();
    }
}
