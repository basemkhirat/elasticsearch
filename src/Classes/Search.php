<?php

namespace Basemkhirat\Elasticsearch\Classes;

use Basemkhirat\Elasticsearch\Query;

/**
 * Class Search
 * @package Basemkhirat\Elasticsearch\Classes
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

    /**
     * Search constructor.
     * @param Query $query
     */
    public function __construct(Query $query, $q, $settings = NULL)
    {
        $this->query = $query;
        $this->q = $q;

        if(is_callback_function($settings)){
            $settings($this);
        }

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

        if($this->boost > 1) {
            $query_params["boost"] = $this->boost;
        }

        if(count($this->fields)){
            $query_params["fields"] = $this->fields;
        }

        $this->query->must[] = [
            "query_string" => $query_params
        ];
    }
}
