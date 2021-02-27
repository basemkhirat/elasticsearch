<?php

namespace Matchory\Elasticsearch\Classes;

use Matchory\Elasticsearch\Query;

/**
 * Class Bulk
 *
 * @package Matchory\Elasticsearch\Classes
 */
class Bulk
{
    /**
     * The query object
     *
     * @var Query
     */
    public $query;

    /**
     * The document key
     *
     * @var string|null
     */
    public $_id;

    /**
     * The index name
     *
     * @var string|null
     */
    public $index;

    /**
     * The type name
     *
     * @var string|null
     */
    public $type;

    /**
     * Bulk body
     *
     * @var array
     */
    public $body = [];

    /**
     * Number of pending operations
     *
     * @var int
     */
    public $operationCount = 0;

    /**
     * Operation count which will trigger autocommit
     *
     * @var int
     */
    public $autocommitAfter = 0;

    /**
     * @param Query    $query
     * @param int|null $autocommitAfter
     */
    public function __construct(Query $query, ?int $autocommitAfter = null)
    {
        $this->query = $query;

        $this->autocommitAfter = (int)$autocommitAfter;
    }

    /**
     * Set the index name
     *
     * @param string|null $index
     *
     * @return $this
     */
    public function index(?string $index = null): self
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Set the type name
     *
     * @param string|null $type
     *
     * @return $this
     */
    public function type(?string $type = null): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Filter by _id
     *
     * @param string|null $_id
     *
     * @return $this
     */
    public function _id(?string $_id = null): self
    {
        $this->_id = $_id;

        return $this;
    }

    /**
     * Just an alias for _id() method
     *
     * @param string|null $_id
     *
     * @return $this
     */
    public function id(?string $_id = null): self
    {
        return $this->_id($_id);
    }

    /**
     * Add pending document for insert
     *
     * @param array $data
     *
     * @return bool
     */
    public function insert(array $data = []): bool
    {
        return $this->action('index', $data);
    }

    /**
     * Add pending document for update
     *
     * @param array $data
     *
     * @return bool
     */
    public function update(array $data = []): bool
    {
        return $this->action('update', $data);
    }

    /**
     * Add pending document for deletion
     *
     * @return bool
     */
    public function delete(): bool
    {
        return $this->action('delete');
    }

    /**
     * Add pending document abstract action
     *
     * @param string $actionType
     * @param array  $data
     *
     * @return bool
     */
    public function action(string $actionType, array $data = []): bool
    {
        $this->body["body"][] = [
            $actionType => [
                '_index' => $this->getIndex(),
                '_type' => $this->getType(),
                '_id' => $this->_id,
            ],
        ];

        if ( ! empty($data)) {
            $this->body["body"][] = $actionType === "update"
                ? ["doc" => $data]
                : $data;
        }

        $this->operationCount++;

        $this->reset();

        if (
            $this->autocommitAfter > 0 &&
            $this->operationCount >= $this->autocommitAfter
        ) {
            return (bool)$this->commit();
        }

        return true;
    }

    /**
     * Get Bulk body
     *
     * @return array
     */
    public function body(): array
    {
        return $this->body;
    }

    /**
     * Reset names
     *
     * @return void
     */
    public function reset(): void
    {
        $this->index();
        $this->type();
    }

    /**
     * Commit all pending operations
     *
     * @return array|null
     */
    public function commit(): ?array
    {
        if (empty($this->body)) {
            return null;
        }

        $result = $this
            ->query
            ->getConnection()
            ->getClient()
            ->bulk($this->body);

        $this->operationCount = 0;
        $this->body = [];

        return $result;
    }

    /**
     * Get the index name
     *
     * @return string|null
     */
    protected function getIndex(): ?string
    {
        return $this->index ?: $this->query->getIndex();
    }

    /**
     * Get the type name
     *
     * @return string|null
     * @deprecated Mapping types are deprecated as of Elasticsearch 6.0.0
     * @see        https://www.elastic.co/guide/en/elasticsearch/reference/7.10/removal-of-types.html
     */
    protected function getType(): ?string
    {
        return $this->type ?: $this->query->getType();
    }
}
