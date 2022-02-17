<?php

namespace Basemkhirat\Elasticsearch\Classes;

use Basemkhirat\Elasticsearch\Query;
use Illuminate\Support\Str;

/**
 * Class Aggregation
 * @package Basemkhirat\Elasticsearch\Classes
 */
class Aggregation
{

    /**
     * The query object
     * @var Query
     */
    public $query;

    /**
     * The agg name
     * @var string
     */
    public $name;

    /**
     * the agg type
     * @var int
     */
    protected $type = null;

    /**
     * The agg field
     * @var array
     */
    public $field = null;

    /**
     * The agg size
     * @var array
     */
    public $size = null;

    /**
     * The agg order
     * @var array
     */
    public $order = null;

    /**
     * The agg parameters
     * @var array
     */
    public $parameters = [];

    /**
     * the agg body
     * @var int
     */
    public $body = [];

    /**
     * Query array aggs depth
     * @var string
     */
    protected $depth = "";

    /**
     * Show if agg is filled in query array
     * @var bool
     */
    protected $filled = false;

    /**
     * set agg field
     * @param $field
     * @return $this
     */
    public function field($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * set agg size
     * @param $size
     * @return $this
     */
    public function take($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * set agg order
     * @param $field
     * @param $direction
     * @return $this
     */
    public function order($field = "_count", $direction = "desc")
    {
        if (is_array($field)) {
            $this->order = $field;
        } else {
            $this->order = [$field => $direction];
        }

        return $this;
    }

    /**
     * set agg type
     * @param $type
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * set agg body
     * @param $body
     * @return $this
     */
    public function body($body = [])
    {
        $this->body = $body;

        return $this;
    }

    /**
     * handle nested aggs
     * @param null $name
     * @param $callback
     */
    public function agg($name = null, $callback)
    {
        $this->fill();

        $this->field = null;
        $this->order = null;
        $this->size = null;
        $this->parameters = [];

        self::set($this->query, $name, $callback, $this);
        
        return $this;
    }

    /**
     * Fill query with current agg
     */
    protected function fill()
    {

        $this->depth .= "aggs." . $this->name . ".";

        $params = [];

        if ($this->field) {
            $params["field"] = $this->field;
        }

        if ($this->size) {
            $params["size"] = $this->size;
        }

        if ($this->order) {
            $params["order"] = $this->order;
        }

        array_set($this->query->body, trim($this->depth, "."), [
            $this->type => array_merge($this->body, array_merge($params, $this->parameters))
        ]);
    }

    /**
     * Create the main agg object
     * @param Query $query
     * @param null $name
     * @param $callback
     * @param null $aggregation
     */
    public static function set(Query $query, $name = null, $callback, $aggregation = null)
    {
        $aggregation = $aggregation ? $aggregation : new self();

        $aggregation->query = $query;
        $aggregation->name = $name;
        $aggregation->body = [];

        if (is_callback_function($callback)) {
            $callback($aggregation);
        }

        if (!$aggregation->filled) {
            $aggregation->fill();
            $aggregation->filled = true;
        }
    }

    public function __call($name, $arguments)
    {
        if (Str::startsWith($name, "set") && strlen($name) > 3) {
            $attr = preg_replace('/^set/', '', $name);
            $this->parameters[Str::snake($attr)] = count($arguments) ? $arguments[0] : null;
            return $this;
        }else{
            abort(500, "Method $name is not found");
        }
    }
}
