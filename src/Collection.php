<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch;

use Illuminate\Support\Collection as BaseCollection;

use function array_map;
use function is_array;
use function json_encode;

class Collection extends BaseCollection
{
    /**
     * @var int|null
     */
    protected $total;

    /**
     * @var int|null
     */
    protected $maxScore;

    /**
     * @var int|null
     */
    protected $duration;

    /**
     * @var bool|null
     */
    protected $timedOut;

    /**
     * @var string|null
     */
    protected $scrollId;

    /**
     * @var mixed|null
     */
    protected $shards;

    public function __construct(
        $items = [],
        ?int $total = null,
        ?int $maxScore = null,
        ?float $duration = null,
        ?bool $timedOut = null,
        ?string $scrollId = null,
        $shards = null
    ) {
        parent::__construct($items);
        $this->items = $items;
        $this->total = $total;
        $this->maxScore = $maxScore;
        $this->duration = $duration;
        $this->timedOut = $timedOut;
        $this->scrollId = $scrollId;
        $this->shards = $shards;
    }

    public static function fromResponse(
        array $response,
        ?array $items = null
    ): self {
        $items = $items ?? $response['hits']['hits'] ?? [];

        $maxScore = (int)$response['hits']['max_score'];
        $duration = (int)$response['took'];
        $timedOut = (bool)$response['timed_out'];
        $scrollId = (string)($response['_scroll_id'] ?? null);
        $shards = (object)$response['_shards'];
        $total = (int)(is_array($response['hits']['total'])
            ? $response['hits']['total']['value']
            : $response['hits']['total']
        );

        return new static(
            $items,
            $total,
            $maxScore,
            $duration,
            $timedOut,
            $scrollId,
            $shards
        );
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function getMaxScore(): ?int
    {
        return $this->maxScore;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function isTimedOut(): ?bool
    {
        return $this->timedOut;
    }

    public function getScrollId(): ?string
    {
        return $this->scrollId;
    }

    public function getShards()
    {
        return $this->shards;
    }

    /**
     * Get the collection of items as Array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(static function ($item) {
            return $item->toArray();
        }, $this->items);
    }

    /**
     * Get the collection of items as JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
