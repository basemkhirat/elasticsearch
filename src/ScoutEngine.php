<?php

namespace Matchory\Elasticsearch;

use Elasticsearch\Client as Elastic;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;

use function array_filter;
use function array_merge;
use function collect;
use function count;

class ScoutEngine extends Engine
{
    /**
     * Index where the models will be saved.
     *
     * @var string
     */
    protected $index;

    /**
     * @var Elastic
     */
    protected $elastic;

    /**
     * ScoutEngine constructor.
     *
     * @param Elastic $elastic
     * @param string  $index
     */
    public function __construct(Elastic $elastic, string $index)
    {
        $this->elastic = $elastic;
        $this->index = $index;
    }

    /**
     * Remove the given model from the index.
     *
     * @param Collection $models
     *
     * @return void
     */
    public function delete($models): void
    {
        $params = [
            'body' => [],
        ];

        $models->each(function (Model $model) use (&$params) {
            $params['body'][] = [
                'delete' => [
                    '_id' => $model->getKey(),
                    '_index' => $this->index,
                    '_type' => $model->searchableAs(),
                ],
            ];
        });

        $this->elastic->bulk($params);
    }

    /**
     * Flush all of the model's records from the engine.
     *
     * @param Model $model
     *
     * @return void
     */
    public function flush($model): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->elastic->deleteByQuery([
            'type' => $model->searchableAs(),
            'index' => $this->index,
            'body' => [
                'query' => [
                    'match_all' => [],
                ],
            ],
        ]);
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param mixed $results
     *
     * @return int
     */
    public function getTotalCount($results): int
    {
        return $results['hits']['total'];
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param Builder $builder
     * @param mixed   $results
     * @param Model   $model
     *
     * @return Collection
     */
    public function map(Builder $builder, $results, $model): Collection
    {
        if ((int)$results['hits']['total'] === 0) {
            return Collection::make();
        }

        $keys = collect($results['hits']['hits'])
            ->pluck('_id')
            ->values()
            ->all();

        $models = $model
            ->whereIn($model->getKeyName(), $keys)
            ->get()
            ->keyBy($model->getKeyName());

        $collection = new Collection($results['hits']['hits']);

        return $collection->map(static function (
            array $hit
        ) use ($models) {
            return $models[$hit['_id']];
        });
    }

    /**
     * @param mixed $results
     *
     * @return Collection
     */
    public function mapIds($results): Collection
    {
        return new Collection([]);
    }

    /**
     * Perform the given search on the engine.
     *
     * @param Builder $builder
     * @param int     $perPage
     * @param int     $page
     *
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        $result = $this->performSearch($builder, [
            'numericFilters' => $this->filters($builder),
            'from' => (($page * $perPage) - $perPage),
            'size' => $perPage,
        ]);

        $result['nbPages'] = $result['hits']['total'] / $perPage;

        return $result;
    }

    /**
     * Perform the given search on the engine.
     *
     * @param Builder $builder
     *
     * @return mixed
     */
    public function search(Builder $builder)
    {
        return $this->performSearch($builder, array_filter([
            'numericFilters' => $this->filters($builder),
            'size' => $builder->limit,
        ]));
    }

    /**
     * Update the given model in the index.
     *
     * @param Collection $models
     *
     * @return void
     */
    public function update($models): void
    {
        $params = [
            'body' => [],
        ];

        $models->each(function (Model $model) use (&$params) {
            $params['body'][] = [
                'update' => [
                    '_id' => $model->getKey(),
                    '_index' => $this->index,
                    '_type' => $model->searchableAs(),
                ],
            ];

            $params['body'][] = [
                'doc' => $model->toSearchableArray(),
                'doc_as_upsert' => true,
            ];
        });

        $this->elastic->bulk($params);
    }

    /**
     * Perform the given search on the engine.
     *
     * @param Builder $builder
     * @param array   $options
     *
     * @return mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $params = [
            'index' => $this->index,
            'type' => $builder->model->searchableAs(),
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'query_string' => [
                                    'query' => $builder->query,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if (isset($options['from'])) {
            $params['body']['from'] = $options['from'];
        }

        if (isset($options['size'])) {
            $params['body']['size'] = $options['size'];
        }

        if (
            isset($options['numericFilters']) &&
            count($options['numericFilters'])
        ) {
            $params['body']['query']['bool']['must'] = array_merge(
                $params['body']['query']['bool']['must'],
                $options['numericFilters']
            );
        }

        return $this->elastic->search($params);
    }

    /**
     * Get the filter array for the query.
     *
     * @param Builder $builder
     *
     * @return array
     */
    protected function filters(Builder $builder): array
    {
        return collect($builder->wheres)
            ->map(
            /**
             * @param mixed      $value
             * @param string|int $key
             *
             * @return array
             */
                static function ($value, $key): array {
                    return ['match_phrase' => [$key => $value]];
                })
            ->values()
            ->all();
    }
}
