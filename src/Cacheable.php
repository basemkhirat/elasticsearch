<?php

namespace Basemkhirat\Elasticsearch;

Trait Cacheable {


    /**
     * The key that should be used when caching the query.
     *
     * @var string
     */
    protected $cacheKey;
    /**
     * The number of minutes to cache the query.
     *
     * @var int
     */
    protected $cacheMinutes;
    /**
     * The tags for the query cache.
     *
     * @var array
     */
    protected $cacheTags;
    /**
     * The cache driver to be used.
     *
     * @var string
     */
    protected $cacheDriver;
    /**
     * A cache prefix.
     *
     * @var string
     */
    protected $cachePrefix = 'rememberable';

    /**
     * Indicate that the query results should be cached.
     *
     * @param  \DateTime|int  $minutes
     * @param  string  $key
     * @return $this
     */
    public function remember($minutes, $key = null)
    {
        list($this->cacheMinutes, $this->cacheKey) = [$minutes, $key];
        return $this;
    }
    /**
     * Indicate that the query results should be cached forever.
     *
     * @param  string  $key
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function rememberForever($key = null)
    {
        return $this->remember(-1, $key);
    }
    /**
     * Indicate that the query should not be cached.
     *
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function dontRemember()
    {
        $this->cacheMinutes = $this->cacheKey = $this->cacheTags = null;
        return $this;
    }
    /**
     * Indicate that the query should not be cached. Alias for dontRemember().
     *
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function doNotRemember()
    {
        return $this->dontRemember();
    }
    /**
     * Indicate that the results, if cached, should use the given cache tags.
     *
     * @param  array|mixed  $cacheTags
     * @return $this
     */
    public function cacheTags($cacheTags)
    {
        $this->cacheTags = $cacheTags;
        return $this;
    }
    /**
     * Indicate that the results, if cached, should use the given cache driver.
     *
     * @param  string  $cacheDriver
     * @return $this
     */
    public function cacheDriver($cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;
        return $this;
    }
    /**
     * Get the cache object with tags assigned, if applicable.
     *
     * @return \Illuminate\Cache\CacheManager
     */
    protected function getCache()
    {
        $cache = Cache::driver($this->cacheDriver);
        return $this->cacheTags ? $cache->tags($this->cacheTags) : $cache;
    }
    /**
     * Get the cache key and cache minutes as an array.
     *
     * @return array
     */
    protected function getCacheInfo()
    {
        return [$this->getCacheKey(), $this->cacheMinutes];
    }
    /**
     * Get a unique cache key for the complete query.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cachePrefix.':'.($this->cacheKey ?: $this->generateCacheKey());
    }
    /**
     * Generate the unique cache key for the query.
     *
     * @return string
     */
    public function generateCacheKey()
    {
        $name = $this->connection->getName();
        return hash('sha256', $name.$this->toSql().serialize($this->getBindings()));
    }
    /**
     * Flush the cache for the current model or a given tag name
     *
     * @param  mixed  $cacheTags
     * @return boolean
     */
    public function flushCache($cacheTags = null)
    {
        if ( ! method_exists(Cache::getStore(), 'tags')) {
            return false;
        }
        $cacheTags = $cacheTags ?: $this->cacheTags;
        Cache::tags($cacheTags)->flush();
        return true;
    }
    /**
     * Get the Closure callback used when caching queries.
     *
     * @param  array  $columns
     * @return \Closure
     */
    protected function getCacheCallback($columns)
    {
        return function () use ($columns) {
            $this->cacheMinutes = null;
            return $this->get($columns);
        };
    }
    /**
     * Set the cache prefix.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function prefix($prefix)
    {
        $this->cachePrefix = $prefix;
        return $this;
    }




}
