<?php

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class SkipTest extends TestCase
{

    use ESQueryTrait;

    /**
     * Test the skip() method.
     *
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testSkipMethod(): void
    {
        self::assertEquals(
            $this->getExpected(10),
            $this->getActual(10)
        );
    }

    /**
     * Get The expected results.
     *
     * @param string $from
     *
     * @return array
     */
    protected function getExpected(string $from): array
    {
        $query = $this->getQueryArray();

        $query["from"] = $from;

        return $query;
    }

    /**
     * Get The actual results.
     *
     * @param string $from
     *
     * @return array
     */
    protected function getActual(string $from): array
    {
        return $this->getQueryObject()->skip($from)->query();
    }
}
