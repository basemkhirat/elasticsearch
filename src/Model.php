<?php

namespace Basemkhirat\Elasticsearch;

use Illuminate\Support\Str;

/**
 * Elasticsearch data model
 * Class Model
 * @package Basemkhirat\Elasticsearch
 */
class Model
{

    /**
     * Model connection name
     * @var string
     */
    protected $connection;

    /**
     * Model index name
     * @var string
     */
    protected $index;

    /**
     * Model type name
     * @var string
     */
    protected $type;

    /**
     * Model selectable fields
     * @var array
     */
    protected $selectable = [];

    /**
     * Model unselectable fields
     * @var array
     */
    protected $unselectable = [];

    /**
     * Model hidden fields
     * @var array
     */
    protected $hidden = [];

    /**
     * Attribute data type
     * @available boolean, bool, integer, int, float, double, string, array, object, null
     * @var array
     */
    protected $casts = [];

    /**
     * Model attributes
     * @var array
     */
    protected $attributes = [];

    /**
     * Model flag indicates row exists in database
     * @var bool
     */
    protected $exists = false;

    /**
     * Additional custom attributes
     * @var array
     */
    protected $appends = [];

    /**
     * Create a new Elasticsearch model instance.
     * @param array $attributes
     * @param bool $exists
     */
    function __construct($attributes = [], $exists = false)
    {
        $this->attributes = $attributes;

        $this->exists = $exists;

        $this->connection = $this->getConnection();
    }

    /**
     * Get current connection
     * @return string
     */
    public function getConnection()
    {
        return $this->connection ? $this->connection : config("es.default");
    }

    /**
     * Set current connection
     * @return void
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get index name
     *
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Set index name
     *
     * @return void
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * Get selectable fields
     *
     * @return void
     */
    public function getSelectable()
    {
        return $this->selectable ? $this->selectable : [];
    }

    /**
     * Get selectable fields
     *
     * @return void
     */
    public function getUnSelectable()
    {
        return $this->unselectable ? $this->unselectable : [];
    }

    /**
     * Get type name
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type name
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Magic getter for model properties
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->attributes)) {

            // Search in original model attributes

            return $this->getOriginalAttribute($name);

        } elseif (in_array($name, $this->appends)) {

            // Search in appends model attributes

            return $this->getAppendsAttribute($name);

        } elseif (property_exists($this, $name)) {

            return $this->$name;

        }

        return NULL;
    }

    /**
     * Get original model attribute
     * @param $name
     * @return mixed
     */
    protected function getOriginalAttribute($name)
    {
        $method = "get" . ucfirst(Str::camel($name)) . "Attribute";
        $value = method_exists($this, $method) ? $this->$method($this->attributes[$name]) : $this->attributes[$name];
        return $this->setAttributeType($name, $value);
    }

    /**
     * Get Appends model attribute
     * @param $name
     * @return mixed
     */
    protected function getAppendsAttribute($name)
    {
        $method = "get" . ucfirst(Str::camel($name)) . "Attribute";
        $value = method_exists($this, $method) ? $this->$method(NULL) : NULL;
        return $this->setAttributeType($name, $value);
    }

    /**
     * Set attributes casting
     * @param $name
     * @param $value
     * @return mixed
     */
    protected function setAttributeType($name, $value)
    {

        $castTypes = [
            "boolean",
            "bool",
            "integer",
            "int",
            "float",
            "double",
            "string",
            "array",
            "object",
            "null"
        ];

        if (array_key_exists($name, $this->casts)) {
            if (in_array($this->casts[$name], $castTypes)) {
                settype($value, $this->casts[$name]);
            }
        }

        return $value;
    }

    /**
     * Get field highlights
     * @param null $field
     * @return mixed
     */
    public function getHighlights($field = null)
    {

        $highlights = $this->attributes["_highlight"];

        if ($field && array_key_exists($field, $highlights)) return $highlights[$field];

        return $highlights;
    }

    /**
     * Get model as array
     * @return array
     */
    public function toArray()
    {

        $attributes = [];

        foreach ($this->attributes as $name => $value) {
            if (!in_array($name, $this->hidden)) {
                $attributes[$name] = $this->getOriginalAttribute($name);
            }
        }

        foreach ($this->appends as $name) {
            $attributes[$name] = $this->getAppendsAttribute($name);
        }

        return $attributes;
    }

    /**
     * Get the collection of items as JSON.
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Handle model properties setter
     * @param $name
     * @param $value
     * @return null
     */
    public function __set($name, $value)
    {

        $method = "set" . ucfirst(Str::camel($name)) . "Attribute";

        $value = method_exists($this, $method) ? $this->$method($value) : $value;

        $value = $this->setAttributeType($name, $value);

        // Check if it's a key. Set model key

        if ($name == "_id") {
            $this->_id = $value;
        }

        $this->attributes[$name] = $value;
    }

    /**
     * Create a new model query
     * @return mixed
     */
    protected function newQuery()
    {
        $query = app("es")->setModel($this);

        $query->connection($this->getConnection());


        if ($index = $this->getIndex()) {
            $query->index($index);
        }

        if ($type = $this->getType()) {
            $query->type($type);
        }

        if ($fields = $this->getSelectable()) $query->select($fields);
        if ($fields = $this->getUnSelectable()) $query->unselect($fields);

        return $query;
    }

    /**
     * Get all model records
     * @return mixed
     */
    public static function all()
    {
        $instance = new static;

        $model = $instance->newQuery()->get();

        return $model;
    }

    /**
     * Get model by key
     * @param $key
     * @return mixed
     */
    public static function find($key)
    {
        $instance = new static;

        $model = $instance->newQuery()->id($key)->take(1)->first();

        if ($model) {
            $model->exists = true;
        }

        return $model;
    }

    /**
     * Delete model record
     * @return $this|bool
     */
    function delete()
    {

        if (!$this->exists()) {
            return false;
        }

        $this->newQuery()->id($this->getID())->delete();

        $this->exists = false;

        return $this;
    }

    /**
     * Save data to model
     * @return string
     */
    public function save()
    {

        $fields = array_except($this->attributes, ["_index", "_type", "_id", "_score"]);

        if ($this->exists()) {

            // Update the current document

            $this->newQuery()->id($this->getID())->update($fields);

        } else {

            // Check if model key exists in items

            if (array_key_exists("_id", $this->attributes)) {
                $created = $this->newQuery()->id($this->attributes["_id"])->insert($fields);
                $this->_id = $this->attributes["_id"];
            } else {
                $created = $this->newQuery()->insert($fields);
                $this->_id = $created->_id;
            }

            $this->setConnection($this->getConnection());
            $this->setIndex($created->_index);

            // Match earlier versions

            $this->_index = $created->_index;
            $this->_type = $this->type;

            $this->exists = true;
        }

        return $this;
    }

    /**
     * Check model is exists
     * @return bool
     */
    function exists()
    {
        return $this->exists;
    }

    /**
     * Get model key
     * @return mixed
     */
    function getID()
    {
        return $this->attributes["_id"];
    }

    /**
     * Handle dynamic static method calls into the method.
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    /**
     * Handle dynamic method calls into the model.
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->newQuery()->$method(...$parameters);
    }
}
