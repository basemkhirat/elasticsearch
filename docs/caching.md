# Query Caching

This package provides a caching layer over the query builder that utilizes Laravel's cache system.

## Basic Caching

To cache a query result, use the `remember` method:

```php
// Cache for 10 minutes
ES::type("my_type")->search("hello")->remember(10)->get();

// Cache with a specific key
ES::type("my_type")->search("hello")->remember(10, "last_documents")->get();
```

## Cache Drivers

You can specify which cache driver to use:

```php
// Cache using Redis
ES::type("my_type")
  ->search("hello")
  ->cacheDriver("redis")
  ->remember(10, "last_documents")
  ->get();

// Cache using Memcached
ES::type("my_type")
  ->search("hello")
  ->cacheDriver("memcached")
  ->remember(10, "last_documents")
  ->get();
```

## Cache Tags

If you're using Redis or Memcached, you can tag your cached queries:

```php
ES::type("my_type")
  ->search("hello")
  ->cacheTags(["search", "documents"])
  ->remember(10)
  ->get();
```

## Clearing Cache

To clear cached results:

```php
// Clear all cache
Cache::flush();

// Clear specific tags (Redis/Memcached only)
Cache::tags(["search", "documents"])->flush();
```

## Cache Configuration

The caching configuration uses Laravel's cache configuration. You can modify the cache settings in your `config/cache.php` file:

```php
return [
    'default' => env('CACHE_DRIVER', 'file'),

    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],
        
        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],
    ],

    'prefix' => env('CACHE_PREFIX', 'laravel'),
];
```
