<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Elasticsearch Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the Elasticsearch connections below you wish
    | to use as your default connection for all work.
    |
    */
    'default' => env('ELASTIC_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the Elasticsearch connections setup for your application.
    | Of course, examples of configuring each Elasticsearch platform.
    |
    */
    'connections' => [
        'default' => [
            'servers' => [
                [
                    'host' => env('ELASTIC_HOST', '127.0.0.1'),
                    'port' => env('ELASTIC_PORT', 9200),
                    'user' => env('ELASTIC_USER', ''),
                    'pass' => env('ELASTIC_PASS', ''),
                    'scheme' => env('ELASTIC_SCHEME', 'http'),
                ],
            ],

            'index' => env('ELASTIC_INDEX', 'my_index'),

            // Elasticsearch handlers
            // 'handler' => new MyCustomHandler(),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Logger
    |--------------------------------------------------------------------------
    |
    | This setting defines which logging channel will be used by the
    | Elasticsearch library to write log messages. You are free to specify any
    | of your logging channels listed inside the "logging" configuration file.
    |
    */
    'logger' => env('ELASTIC_LOGGER'),

    /*
    |--------------------------------------------------------------------------
    | Elasticsearch Indices
    |--------------------------------------------------------------------------
    |
    | Here you can define your indices, with separate settings and mappings.
    | Edit settings and mappings and run 'php artisan es:index:update' to update
    | indices on elasticsearch server.
    |
    | 'my_index' is just for test. Replace it with a real index name.
    |
    */
    'indices' => [
        'my_index_1' => [
            'aliases' => [
                'my_index',
            ],

            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
                "index.mapping.ignore_malformed" => false,

                "analysis" => [
                    "filter" => [
                        "english_stop" => [
                            "type" => "stop",
                            "stopwords" => "_english_",
                        ],
                        "english_keywords" => [
                            "type" => "keyword_marker",
                            "keywords" => ["example"],
                        ],
                        "english_stemmer" => [
                            "type" => "stemmer",
                            "language" => "english",
                        ],
                        "english_possessive_stemmer" => [
                            "type" => "stemmer",
                            "language" => "possessive_english",
                        ],
                    ],
                    "analyzer" => [
                        "rebuilt_english" => [
                            "tokenizer" => "standard",
                            "filter" => [
                                "english_possessive_stemmer",
                                "lowercase",
                                "english_stop",
                                "english_keywords",
                                "english_stemmer",
                            ],
                        ],
                    ],
                ],
            ],

            'mappings' => [
                'posts' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'english',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
