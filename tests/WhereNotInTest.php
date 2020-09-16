<?php

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class WhereNotInTest extends TestCase
{

    use ESQueryTrait;

    /**
     * Test the whereNotIn() method.
     *
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testWhereNotInMethod(): void
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

        $query["body"]["query"]["bool"]["must_not"][] = [
            "terms" => [$name => $value],
        ];

        return $query;
    }

    /**
     * Get The actual results.
     *
     * @param       $name
     * @param array $value
     *
     * @return mixed
     */
    protected function getActual($name, $value = [])
    {
        return $this
            ->getQueryObject()
            ->whereNotIn($name, $value)
            ->query();
    }
}
