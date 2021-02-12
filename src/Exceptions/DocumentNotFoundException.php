<?php

declare(strict_types=1);

namespace Matchory\Elasticsearch\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Matchory\Elasticsearch\Model;

use function count;
use function implode;

class DocumentNotFoundException extends ModelNotFoundException
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
     * Set the affected Eloquent model and instance ids.
     *
     * @param class-string<Model> $model
     * @param string|array        $ids
     *
     * @return $this
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function setModel($model, $ids = []): self
    {
        $this->model = $model;
        $this->ids = Arr::wrap($ids);

        $this->message = "No query results for model [{$model}]";

        $this->message = count($this->ids) > 0
            ? $this->message . ' ' . implode(', ', $this->ids)
            : $this->message . '.';

        return $this;
    }
}
