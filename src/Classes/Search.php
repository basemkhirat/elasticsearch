<?php

namespace CarlosOCarvalho\Elasticsearch\Classes;

use CarlosOCarvalho\Elasticsearch\Query;

/**
 * Class Search
 * @package CarlosOCarvalho\Elasticsearch\Classes
 */
class Search
{

    /**
     * The query object
     * @var Query
     */
    public $query;

    /**
     * The search query string
     * @var string
     */
    public $q;

    /**
     * The search query boost factor
     * @var integer
     */
    public $boost;

    /**
     * The search fields
     * @var array
     */
    public $fields = [];

    public $queryType; // best_fields | most_fields | cross_fields | phrase | simple_query

    public $default_operator = 'OR';

    public $analyzer = null;
    /**
     * Search constructor.
     * @param Query $query
     */
    public function __construct(Query $query, $q, $settings = null, $queryType = null)
    {
        $this->query = $query;
        $this->q = $q;

        if (is_callback_function($settings)) {
            $settings($this);
        }
        $this->queryType = $queryType ? $queryType : 'simple_query_string';
        $this->settings = $settings;
    }

    /**
     * Set searchable fields
     * @param array $fields
     * @return $this
     */
    public function fields($fields = [])
    {

        $searchable = [];

        foreach ($fields as $field => $weight) {
            $weight_suffix = $weight > 1 ? "^$weight" : "";
            $searchable[] = $field . $weight_suffix;
        }

        $this->fields = $searchable;

        return $this;
    }

    /**
     * Set search boost factor
     * @param int $boost
     * @return $this
     */
    public function boost($boost = 1)
    {
        $this->boost = $boost;

        return $this;
    }

    /**
     * Build the native query
     */
    public function build()
    {

        $query_params = [];

        $query_params["query"] = $this->q;

        if ($this->boost > 1) {
            $query_params["boost"] = $this->boost;
        }

        if (count($this->fields)) {
            $query_params["fields"] = $this->fields;
        }

        if ($this->queryType == 'simple_query_string') {
            $query = [
                    'fields' => count($this->fields) ? $this->fields : [],
                    'query'  => $this->q,
                    'default_operator' => $this->default_operator
            ];

            if ($this->analyzer) {
                $query['analyzer']  =  $this->analyzer;
            }
            $this->query->must[] = [
                "simple_query_string" =>  $query
            ];
        } else {
            $query = [
                    'type' => $this->queryType,
                    'fields' => count($this->fields) ? $this->fields : [],
                    'query'  => $this->q,
                    'default_operator' => $this->default_operator
            ];

            if ($this->analyzer) {
                $query['analyzer']  =  $this->analyzer;
            }
            $this->query->must[] = [
                "multi_match" => $query
            ];
        }
    }
}
