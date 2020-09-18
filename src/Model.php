<?php

namespace Matchory\Elasticsearch;

use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

use function array_diff;
use function array_key_exists;
use function in_array;
use function json_encode;
use function method_exists;
use function property_exists;
use function settype;
use function ucfirst;

/**
 * Elasticsearch data model
 *
 * @property string|null _id
 * @property string|null _index
 * @property string|null _type
 * @property string|null _score
 * @property string|null _highlight
 * @package Matchory\Elasticsearch
 */
class Model
{
    protected const FIELD_ID = '_id';

    /**
     * Model connection name
     *
     * @var string
     */
    protected $connection;

    /**
     * Model index name
     *
     * @var string
     */
    protected $index;

    /**
     * Model type name
     *
     * @var string
     */
    protected $type;

    /**
     * Model selectable fields
     *
     * @var array
     */
    protected $selectable = [];

    /**
     * Model unselectable fields
     *
     * @var array
     */
    protected $unselectable = [];

    /**
     * Model hidden fields
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Attribute data type
     *
     * @available boolean, bool, integer, int, float, double, string, array, object, null
     * @var array
     */
    protected $casts = [];

    /**
     * Model attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Model flag indicates row exists in database
     *
     * @var bool
     */
    protected $exists = false;

    /**
     * Additional custom attributes
     *
     * @var array
     */
    protected $appends = [];

    /**
     * Create a new Elasticsearch model instance.
     *
     * @param array $attributes
     * @param bool  $exists
     */
    public function __construct($attributes = [], $exists = false)
    {
        $this->attributes = $attributes;

        $this->exists = $exists;

        $this->connection = $this->getConnection();
    }

    /**
     * Get all model records
     *
     * @return mixed
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function all(): Collection
    {
        $instance = new static();

        return $instance->newQuery()->get();
    }

    /**
     * Get model by key
     *
     * @param string $key
     *
     * @return Model|null
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function find(string $key): ?Model
    {
        $instance = new static();

        $model = $instance
            ->newQuery()
            ->id($key)
            ->take(1)
            ->first();

        if ($model) {
            $model->exists = true;
        }

        return $model;
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        return (new static())->$method(...$parameters);
    }

    /**
     * Get current connection name
     *
     * @return string
     */
    public function getConnection(): string
    {
        return $this->connection ?: config('es.default');
    }

    /**
     * Set current connection name
     *
     * @param string $connection
     *
     * @return void
     */
    public function setConnection(string $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * Get index name
     *
     * @return string|null
     */
    public function getIndex(): ?string
    {
        return $this->index;
    }

    /**
     * Set index name
     *
     * @param string $index
     *
     * @return void
     */
    public function setIndex(string $index): void
    {
        $this->index = $index;
    }

    /**
     * Get selectable fields
     *
     * @return array
     */
    public function getSelectable(): array
    {
        return $this->selectable ?: [];
    }

    /**
     * Get selectable fields
     *
     * @return array
     */
    public function getUnSelectable(): array
    {
        return $this->unselectable ?: [];
    }

    /**
     * Get type name
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set type name
     *
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Magic getter for model properties
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        // Search in original model attributes
        if (array_key_exists($name, $this->attributes)) {
            return $this->getOriginalAttribute($name);
        }

        // Search in appends model attributes
        if (in_array($name, $this->appends, true)) {
            return $this->getAppendsAttribute($name);
        }

        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return null;
    }

    /**
     * Handle model properties setter
     *
     * @param $name
     * @param $value
     *
     * @return void
     */
    public function __set(string $name, $value): void
    {
        $method = sprintf('set%sAttribute', ucfirst(Str::camel(
            $name
        )));

        $value = method_exists($this, $method)
            ? $this->$method($value)
            : $value;

        $value = $this->setAttributeType($name, $value);

        // Check if it's a key. Set model key
        if ($name === self::FIELD_ID) {
            $this->_id = $value;
        }

        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        if ($name === self::FIELD_ID) {
            return isset($this->_id);
        }

        return isset($this->attributes[$name]);
    }

    /**
     * Get field highlights
     *
     * @param string|null $field
     *
     * @return mixed
     */
    public function getHighlights($field = null)
    {
        $highlights = $this->attributes['_highlight'];

        if ($field && array_key_exists($field, $highlights)) {
            return $highlights[$field];
        }

        return $highlights;
    }

    /**
     * Get model as array
     *
     * @return array
     */
    public function toArray(): array
    {
        $attributes = [];

        foreach ($this->attributes as $name => $value) {
            if ( ! in_array($name, $this->hidden, true)) {
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
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Delete model record
     *
     * @return $this
     */
    public function delete(): ?self
    {
        if ( ! $this->exists()) {
            return null;
        }

        $this
            ->newQuery()
            ->id($this->getID())
            ->delete();

        $this->exists = false;

        return $this;
    }

    /**
     * Save data to model
     *
     * @return string
     */
    public function save(): string
    {
        $fields = array_diff($this->attributes, [
            '_index',
            '_type',
            '_id',
            '_score',
        ]);

        if ($this->exists()) {
            // Update the current document

            $this
                ->newQuery()
                ->id($this->getID())
                ->update($fields);
        } else {
            // Check if model key exists in items

            if (array_key_exists('_id', $this->attributes)) {
                $created = $this
                    ->newQuery()
                    ->id($this->attributes['_id'])
                    ->insert($fields);
                $this->_id = $this->attributes['_id'];
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
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->exists;
    }

    /**
     * Get model key
     *
     * @return mixed
     */
    public function getID()
    {
        return $this->attributes['_id'];
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->newQuery()->$method(...$parameters);
    }

    /**
     * Get original model attribute
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function getOriginalAttribute(string $name)
    {
        $method = sprintf('get%sAttribute', ucfirst(Str::camel(
            $name
        )));

        $value = method_exists($this, $method)
            ? $this->$method($this->attributes[$name])
            : $this->attributes[$name];

        return $this->setAttributeType($name, $value);
    }

    /**
     * Get Appends model attribute
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function getAppendsAttribute(string $name)
    {
        $method = sprintf('get%sAttribute', ucfirst(Str::camel(
            $name
        )));

        $value = method_exists($this, $method)
            ? $this->$method(null)
            : null;

        return $this->setAttributeType($name, $value);
    }

    /**
     * Set attributes casting
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function setAttributeType(string $name, $value)
    {
        $castTypes = [
            'boolean',
            'bool',
            'integer',
            'int',
            'float',
            'double',
            'string',
            'array',
            'object',
            'null',
        ];

        if (
            array_key_exists($name, $this->casts) &&
            in_array(
                $this->casts[$name],
                $castTypes,
                true
            )
        ) {
            settype($value, $this->casts[$name]);
        }

        return $value;
    }

    /**
     * Create a new model query
     *
     * @return Query
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function newQuery(): Query
    {
        /** @var Connection $elastic */
        $elastic = app('es');
        $query = $elastic->connection($this->getConnection());
        $query->setModel($this);

        if ($index = $this->getIndex()) {
            $query->index($index);
        }

        if ($type = $this->getType()) {
            $query->type($type);
        }

        if ($fields = $this->getSelectable()) {
            $query->select($fields);
        }

        if ($fields = $this->getUnSelectable()) {
            $query->unselect($fields);
        }

        return $query;
    }
}
