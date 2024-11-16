# Configuration

After publishing, two configuration files will be created.

## Elasticsearch Configuration

The `config/es.php` file is where you can add more than one elasticsearch server:

```php
# Here you can define the default connection name.
'default' => env('ELASTIC_CONNECTION', 'default'),

# Here you can define your connections.
'connections' => [
    'default' => [
        'servers' => [
            [
                "host" => env("ELASTIC_HOST", "127.0.0.1"),
                "port" => env("ELASTIC_PORT", 9200),
                'user' => env('ELASTIC_USER', ''),
                'pass' => env('ELASTIC_PASS', ''),
                'scheme' => env('ELASTIC_SCHEME', 'http'),
            ]
        ],
        
        // Custom handlers
        // 'handler' => new MyCustomHandler(),
        
        'index' => env('ELASTIC_INDEX', 'my_index')
    ]
],
 
# Here you can define your indices.
'indices' => [
    'my_index_1' => [
        "aliases" => [
            "my_index"
        ],
        'settings' => [
            "number_of_shards" => 1,
            "number_of_replicas" => 0,
        ],
        'mappings' => [
            'posts' => [
                'properties' => [
                    'title' => [
                        'type' => 'string'
                    ]
                ]
            ]
        ]
    ]
]
```

## Scout Configuration

The package can be used as a Laravel Scout driver. After following [Laravel Scout installation](https://laravel.com/docs/5.4/scout#installation), update these lines in `config/scout.php` configuration file:

```php
// Set the driver to elasticsearch
'driver' => env('SCOUT_DRIVER', 'elasticsearch'),

// Add elasticsearch configuration
'elasticsearch' => [
    'index' => env('ELASTIC_INDEX', 'default'),
    'config' => [
        'hosts' => [
            env('ELASTIC_HOST', 'localhost:9200'),
        ],
    ],
],
```
