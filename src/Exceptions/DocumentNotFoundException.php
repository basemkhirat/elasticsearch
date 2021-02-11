<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch\Exceptions;

use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Arr;

use function count;
use function implode;

class DocumentNotFoundException extends RecordsNotFoundException
{
    /**
     * Name of the affected Elasticsearch model.
     *
     * @var string
     */
    protected $model;

    /**
     * The affected model IDs.
     *
     * @var string|array
     */
    protected $ids;

    /**
     * Get the affected Eloquent model.
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Set the affected Eloquent model and instance ids.
     *
     * @param string       $model
     * @param string|array $ids
     *
     * @return $this
     */
    public function setModel(string $model, $ids = []): self
    {
        $this->model = $model;
        $this->ids = Arr::wrap($ids);

        $this->message = "No query results for model [{$model}]";

        $this->message = count($this->ids) > 0
            ? $this->message . ' ' . implode(', ', $this->ids)
            : $this->message . '.';

        return $this;
    }

    /**
     * Get the affected Eloquent model IDs.
     *
     * @return string|array
     */
    public function getIds()
    {
        return $this->ids;
    }
}
