<?php

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class OrderTest extends TestCase
{
    use ESQueryTrait;

    /**
     * Test the orderBy() method.
     *
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testOrderByMethod(): void
    {
        self::assertEquals(
            $this->getActual('created_at', 'asc'),
            $this->getExpected('created_at', 'asc')
        );

        self::assertEquals(
            $this->getExpected('_score'),
            $this->getActual('_score')
        );
    }

    /**
     * Get The expected results.
     *
     * @param string $field
     * @param string $direction
     *
     * @return array
     */
    protected function getExpected(
        string $field,
        string $direction = 'desc'
    ): array {
        $query = $this->getQueryArray();

        $query["body"]["sort"][] = [$field => $direction];

        return $query;
    }

    /**
     * Get The actual results.
     *
     * @param string $field
     * @param string $direction
     *
     * @return array
     */
    protected function getActual(
        string $field,
        string $direction = 'desc'
    ): array {
        return $this
            ->getQueryObject()
            ->orderBy($field, $direction)
            ->query();
    }
}
