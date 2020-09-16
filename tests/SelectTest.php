<?php

namespace Matchory\Elasticsearch\Tests;

use Matchory\Elasticsearch\Tests\Traits\ESQueryTrait;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class SelectTest extends TestCase
{

    use ESQueryTrait;

    /**
     * Test the select() method.
     *
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testIgnoreMethod(): void
    {
        self::assertEquals(
            $this->getExpected("title", "content"),
            $this->getActual("title", "content")
        );
    }

    /**
     * Get The expected results.
     *
     * @return array
     */
    protected function getExpected(): array
    {
        $query = $this->getQueryArray();

        $query["body"]["_source"]['include'] = func_get_args();
        $query["body"]["_source"]['exclude'] = [];

        return $query;
    }

    /**
     * Get The actual results.
     *
     * @return array
     */
    protected function getActual(): array
    {
        return $this
            ->getQueryObject()
            ->select(func_get_args())
            ->query();
    }
}
