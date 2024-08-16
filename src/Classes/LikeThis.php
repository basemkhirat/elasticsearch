<?php

namespace Basemkhirat\Elasticsearch\Classes;

use Basemkhirat\Elasticsearch\Query;

/**
 * Class LikeThis
 * @package Basemkhirat\Elasticsearch\Classes
 */
class LikeThis
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
     * The search fields
     * @var array
     */
    public $fields = [];

    /**
     * the min_term_freq
     * @var int
     */
    protected $min_term_freq = null;

    /**
     * the max_query_terms
     * @var int
     */
    protected $max_query_terms = null;

    /**
     * The min_word_length
     * @var int
     */
    protected $min_word_length = null;

    /**
     * The max_word_length
     * @var int
     */
    protected $max_word_length = null;

    /**
     * The min_should_match
     * @var int
     */
    protected $minimum_should_match = null;

    /**
     * The stop_words
     * @var int
     */
    protected $stop_words = [];

    /**
     * LikeThis constructor.
     * @param Query $query
     */
    public function __construct(Query $query, $q, $settings = NULL)
    {
        $this->query = $query;
        $this->q = $q;

        if (is_callback_function($settings)) {
            $settings($this);
        }

        $this->settings = $settings;
    }

    /**
     * Set fields
     * @param array $fields
     * @return $this
     */
    public function fields($fields = [])
    {

        $args = func_get_args();

        $all_fields = [];

        foreach ($args as $arg) {
            if (is_array($arg)) {
                $all_fields = array_merge($all_fields, $arg);
            } else {
                $all_fields[] = $arg;
            }
        }

        $this->fields = $all_fields;

        return $this;
    }

    /**
     * set min_term_freq
     * @param $min_term_freq
     * @return $this
     */
    public function minTermFreq($min_term_freq)
    {
        $this->min_term_freq = $min_term_freq;

        return $this;
    }

    /**
     * set max_query_terms
     * @param $max_query_terms
     * @return $this
     */
    public function maxQueryTerms($max_query_terms)
    {
        $this->max_query_terms = $max_query_terms;

        return $this;
    }

    /**
     * set min_word_length
     * @param $min_word_length
     * @return $this
     */
    public function minWordLength($min_word_length)
    {
        $this->min_word_length = $min_word_length;

        return $this;
    }

    /**
     * set max_word_length
     * @param $max_word_length
     * @return $this
     */
    public function maxWordLength($max_word_length)
    {
        $this->max_word_length = $max_word_length;

        return $this;
    }

    /**
     * set stop_words
     * @param $stop_words
     * @return $this
     */
    public function stopWords($stop_words)
    {
        $this->stop_words = $stop_words;

        return $this;
    }

    /**
     * set minimum_should_match
     * @param $minimum_should_match
     * @return $this
     */
    public function minimumShouldMatch($minimum_should_match)
    {
        $this->minimum_should_match = $minimum_should_match;

        return $this;
    }

    /**
     * Build the native query
     */
    public function build()
    {
        $parameters = [
            "fields" => $this->fields,
            "like" => $this->q,
        ];

        if($this->min_term_freq) {
            $parameters["min_term_freq"] = $this->min_term_freq;
        }

        if($this->max_query_terms) {
            $parameters["max_query_terms"] = $this->max_query_terms;
        }

        if($this->min_word_length) {
            $parameters["min_word_length"] = $this->min_word_length;
        }

        if($this->max_word_length) {
            $parameters["max_word_length"] = $this->max_word_length;
        }

        if(count($this->stop_words)) {
            $parameters["stop_words"] = $this->stop_words;
        }

        if($this->minimum_should_match) {
            $parameters["minimum_should_match"] = $this->minimum_should_match;
        }

        $this->query->must[] = [
            "more_like_this" => $parameters
        ];
    }
}
