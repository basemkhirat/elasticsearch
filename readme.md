<p align="center">

<a href="https://travis-ci.org/basemkhirat/elasticsearch"><img src="https://travis-ci.org/basemkhirat/elasticsearch.svg?branch=master" alt="Build Status"></a>

<a href="https://packagist.org/packages/basemkhirat/elasticsearch"><img src="https://poser.pugx.org/basemkhirat/elasticsearch/v/stable.svg" alt="Latest Stable Version"></a>

<a href="https://packagist.org/packages/basemkhirat/elasticsearch"><img src="https://poser.pugx.org/basemkhirat/elasticsearch/d/total.svg" alt="Total Downloads"></a>

<a href="https://packagist.org/packages/basemkhirat/elasticsearch"><img src="https://poser.pugx.org/basemkhirat/elasticsearch/license.svg" alt="License"></a>

</p>

<p align="center"><img src="http://basemkhirat.com/images/basemkhirat-elasticsearch.png?123"></p>


## Laravel, Lumen and Native php elasticseach query builder to build complex queries using an elegant syntax

- Keeps you away from wasting your time by replacing array queries with a simple and elegant syntax you will love.
- Feeling free to create, drop, mapping and reindexing throw easy artisan console commands.
- Lumen framework support.
- Native php and composer based applications support.
- Can be used as a [laravel scout](https://laravel.com/docs/5.4/scout) driver.
- Dealing with multiple elasticsearch connections at the same time.
- Awesome pagination based on [LengthAwarePagination](https://github.com/illuminate/pagination).
- Caching queries using a caching layer over query builder built on [laravel cache](https://laravel.com/docs/5.4/cache).

## Requirements

- `php` >= 5.6.6 
  
  See [Travis CI Builds](https://travis-ci.org/basemkhirat/elasticsearch).

- `laravel/laravel` >= 5.* or `laravel/lumen` >= 5.* or `composer application`

## Installation

### <u>Laravel Installation</u>


##### 1) Install package using composer.

```bash
$ composer require basemkhirat/elasticsearch
```

##### 2) Add package service provider.

```php
Basemkhirat\Elasticsearch\ElasticsearchServiceProvider::class
```

##### 3) Add package alias.

```php
'ES' => Basemkhirat\Elasticsearch\Facades\ES::class
```
	
##### 4) Publishing.

```bash
$ php artisan vendor:publish --provider="Basemkhirat\Elasticsearch\ElasticsearchServiceProvider"
```

### <u>Lumen Installation</u>

##### 1) Install package using composer.
```bash
$ composer require basemkhirat/elasticsearch
```

##### 2) Add package service provider in `bootstrap/app.php`.

```php
$app->register(Basemkhirat\Elasticsearch\ElasticsearchServiceProvider::class);
```
	
##### 3) Copy package config directory `vendor/basemkhirat/elasticsearch/src/config` to root folder alongside with `app` directory.
	
	
##### 4) Making Lumen work with facades by uncommenting this line in `bootstrap/app.php`.

```php
$app->withFacades();
```

If you don't want to enable working with Lumen facades you can access the query builder using `app("es")`.

```php
app("es")->index("my_index")->type("my_type")->get();
# is similar to 
ES::index("my_index")->type("my_type")->get();
```   
   
### <u>Composer Installation</u>

You can install package with any composer-based applications

##### 1) Install package using composer.

```bash
$ composer require basemkhirat/elasticsearch
```

##### 2) Creating a connection.

```php
require "vendor/autoload.php";

use Basemkhirat\Elasticsearch\Connection;

$connection = Connection::create([
    'servers' => [
        [
            "host" => '127.0.0.1',
            "port" => 9200,
            'user' => '',
            'pass' => '',
            'scheme' => 'http',
        ]
    ],
    'index' => 'my_index',
]);


# access the query builder using created connection

$documents = $connection->search("hello")->get();
```


## Configuration (Laravel & Lumen)

  
  After publishing, two configuration files will be created.
  
  - `config/es.php` where you can add more than one elasticsearch server.

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
	            'title' => [
	                'type' => 'string'
	            ]
	        ]
	    ]
	]
]

```
  
  - `config/scout.php` where you can use package as a laravel scout driver.

## Working with console environment (Laravel & Lumen)

With some artisan commands you can do some tasks such as creating or updating settings, mappings and aliases.

Note that all commands are running with `--connection=default` option, you can change it throw the command.

These are all available commands:

#### List All indices on server

```bash
$ php artisan es:indices:list

+----------------------+--------+--------+----------+------------------------+-----+-----+------------+--------------+------------+----------------+
| configured (es.php)  | health | status | index    | uuid                   | pri | rep | docs.count | docs.deleted | store.size | pri.store.size |
+----------------------+--------+--------+----------+------------------------+-----+-----+------------+--------------+------------+----------------+
| yes                  | green  | open   | my_index | 5URW60KJQNionAJgL6Q2TQ | 1   | 0   | 0          | 0            | 260b       | 260b           |
+----------------------+--------+--------+----------+------------------------+-----+-----+------------+--------------+------------+----------------+

```

#### Create indices defined in `es.php` config file

Note that creating operation skips the index if exists.

```bash
# Create all indices in config file.

$ php artisan es:indices:create

# Create only 'my_index' index in config file

$ php artisan es:indices:create my_index 

```

#### Update indices defined in `es.php` config file

Note that updating operation updates indices setting, aliases and mapping and doesn't delete the indexed data.

```bash
# Update all indices in config file.

$ php artisan es:indices:update

# Update only 'my_index' index in config file

$ php artisan es:indices:update my_index 

```

#### Drop index

Be careful when using this command, you will lose your index data!

Running drop command with `--force` option will skip all confirmation messages.

```bash
# Drop all indices in config file.

$ php artisan es:indices:drop

# Drop specific index on sever. Not matter for index to be exist in config file or not.

$ php artisan es:indices:drop my_index 

```


#### Reindexing data (with zero downtime)

##### First, why reindexing?

Changing index mapping doesn't reflect without data reindexing, otherwise your search results will not work on the right way.

To avoid down time, your application should work with index `alias` not index `name`.

The index `alias` is a constant name that application should work with to avoid change index names.

##### Assume that we want to change mapping for `my_index`, this is how to do that:

1) Add `alias` as example `my_index_alias` to `my_index` configuration and make sure that application is working with.

```php
"aliases" => [
    "my_index_alias"
]       
```

2) Update index with command:

```bash
$ php artisan es:indices:update my_index
```

3) Create a new index as example `my_new_index` with your new mapping in configuration file.

```bash
$ php artisan es:indices:create my_new_index
```

4) Reindex data from `new_index` into `my_new_index` with command:

```bash
$ php artisan es:indices:reindex my_index my_new_index

# you can control bulk size. Adjust it with your server.

$ php artisan es:indices:reindex my_index my_new_index --size=2000

# you can skip reindexing errors such as mapper parsing exceptions.

$ php artisan es:indices:reindex my_index my_new_index --size=2000 --skip-errors
```

5) Remove `my_index_alias` alias from `my_index` and add it to `my_new_index` in configuration file and update with command:

```bash
$ php artisan es:indices:update
```


## Usage as a Laravel Scout driver

First, follow [Laravel Scout installation](https://laravel.com/docs/5.4/scout#installation).

All you have to do is updating these lines in `config/scout.php` configuration file.

```php
# change the default driver to `es`
	
'driver' => env('SCOUT_DRIVER', 'es'),
	
# link `es` driver with default elasticsearch connection in config/es.php
	
'es' => [
    'connection' => env('ELASTIC_CONNECTION', 'default'),
],
```

Have a look at [laravel Scout documentation](https://laravel.com/docs/5.4/scout#configuration).

## Usage as a query builder

#### Creating a new index

```php
ES::create("my_index");
    
# or 
    
ES::index("my_index")->create();
```
    
##### Creating index with custom options (optional)
   
```php
ES::index("my_index")->create(function($index){
        
    $index->shards(5)->replicas(1)->mapping([
        'my_type' => [
            'properties' => [
                'first_name' => [
                    'type' => 'string',
                ],
                'age' => [
                    'type' => 'integer'
                ]
            ]
        ]
    ])
    
});
    
# or
    
ES::create("my_index", function($index){
  
      $index->shards(5)->replicas(1)->mapping([
          'my_type' => [
              'properties' => [
                  'first_name' => [
                      'type' => 'string',
                  ],
                  'age' => [
                      'type' => 'integer'
                  ]
              ]
          ]
      ])
  
});

```
#### Dropping index

```php
ES::drop("my_index");
    
# or
    
ES::index("my_index")->drop();
```
#### Running queries
```php
$documents = ES::connection("default")
                ->index("my_index")
                ->type("my_type")
                ->get();    # return a collection of results
```
You can rewrite the above query to

```php
$documents = ES::type("my_type")->get();    # return a collection of results
```

The query builder will use the default connection, index name in configuration file `es.php`. 
 
Connection and index names in query overrides connection and index names in configuration file `es.php`.

##### Getting document by id
```php
$documents = ES::id(3)->get();
    
# or
    
$documents = ES::_id(3)->get();
```
##### Sorting
```php 
$documents = ES::orderBy("created_at", "desc")->get();
    
# Sorting with text search score
    
$documents = ES::orderBy("_score")->get();
```
##### Limit and offset
```php
$documents = ES::take(10)->skip(5)->get();
```
##### Select only specific fields
```php    
$documents = ES::select("title", "content")->take(10)->skip(5)->get();
```
##### Where clause
```php    
ES::where("views", 150)->get(); or ES::where("views", "=", 150)->get();
```
##### Where greater than
```php
ES::where("views", ">", 150)->get();
```
##### Where greater than or equal
```php
ES::where("views", ">=", 150)->get();
```
##### Where less than
```php
ES::where("views", "<", 150)->get();
```
##### Where greater than or equal
```php
ES::where("views", "<=", 150)->get();
```
##### Where like
```php
ES::where("title", "like", "foo")->get();
```
##### Where field exists
```php
ES::where("hobbies", "exists", true)->get(); 
# or 
ES::whereExists("hobbies", true)->get();
```    
##### Where in clause
```php    
ES::whereIn("id", [100, 150])->get();
```
##### Where between clause 
```php    
ES::whereBetween("id", 100, 150)->get();
```    
##### Where not clause
```php    
ES::whereNot("views", 150)->get(); 
#or
ES::whereNot("views", "=", 150)->get();
```
##### Where not greater than
```php
ES::whereNot("views", ">", 150)->get();
```
##### Where not greater than or equal
```php
ES::whereNot("views", ">=", 150)->get();
```
##### Where not less than
```php
ES::whereNot("views", "<", 150)->get();
```
##### Where not less than or equal
```php
ES::whereNot("views", "<=", 150)->get();
```
##### Where not like
```php
ES::whereNot("title", "like", "foo")->get();
```
##### Where not field exists
```php
ES::whereNot("hobbies", "exists", true)->get(); 
# or
ES::whereExists("hobbies", true)->get();
```    
##### Where not in clause
```php    
ES::whereNotIn("id", [100, 150])->get();
```
##### Where not between clause 
```php    
ES::whereNotBetween("id", 100, 150)->get();
```
   
##### Search by a distance from a geo point 
```php  
ES::distance("location", ["lat" => -33.8688197, "lon" => 151.20929550000005], "10km")->get();
# or
ES::distance("location", "-33.8688197,151.20929550000005", "10km")->get();
# or
ES::distance("location", [151.20929550000005, -33.8688197], "10km")->get();  
```
  
##### Search the entire document
    
```php
ES::search("bar")->get();
    
# search with Boost = 2
    
ES::search("bar", 2)->get();
```

##### Return only first record

```php    
ES::search("bar")->first();
```
  
##### Return only count
```php    
ES::search("bar")->count();
```
    
##### Scan-and-Scroll queries
    
 These queries are suitable for large amount of data. 
    A scrolled search allows you to do an initial search and to keep pulling batches of results
    from Elasticsearch until there are no more results left. It’s a bit like a cursor in a traditional database

```php    
$documents = ES::search("foo")
                 ->scroll("2m")
                 ->take(1000)
                 ->get();
```              
  Response will contain a hashed code `scroll_id` will be used to get the next result by running

```php
$documents = ES::search("foo")
                 ->scroll("2m")
                 ->scrollID("DnF1ZXJ5VGhlbkZldGNoBQAAAAAAAAFMFlJQOEtTdnJIUklhcU1FX2VqS0EwZncAAAAAAAABSxZSUDhLU3ZySFJJYXFNRV9laktBMGZ3AAAAAAAAAU4WUlA4S1N2ckhSSWFxTUVfZWpLQTBmdwAAAAAAAAFPFlJQOEtTdnJIUklhcU1FX2VqS0EwZncAAAAAAAABTRZSUDhLU3ZySFJJYXFNRV9laktBMGZ3")
                 ->get();
```
                        
   And so on ...
    
   Note that you don't need to write the query parameters in every scroll.
    All you need the `scroll_id` and query scroll time.
    
   To clear `scroll_id` 
    
```php  
ES::scrollID("DnF1ZXJ5VGhlbkZldGNoBQAAAAAAAAFMFlJQOEtTdnJIUklhcU1FX2VqS0EwZncAAAAAAAABSxZSUDhLU3ZySFJJYXFNRV9laktBMGZ3AAAAAAAAAU4WUlA4S1N2ckhSSWFxTUVfZWpLQTBmdwAAAAAAAAFPFlJQOEtTdnJIUklhcU1FX2VqS0EwZncAAAAAAAABTRZSUDhLU3ZySFJJYXFNRV9laktBMGZ3")
        ->clear();
```
    
##### Paginate results with per_page = 5

```php   
$documents = ES::search("bar")->paginate(5);
    
# getting pagination links
    
$documents->links();

```

##### Getting the query array without execution

```php
$query = ES::search("foo")->where("views", ">", 150)->query();
```

##### Getting the original elasticsearch response

```php
$query = ES::search("foo")->where("views", ">", 150)->response();
```

##### Ignoring bad HTTP response

```php      
$documents = ES::ignore(404, 500)->id(5)->first();
```

##### Query Caching (Laravel & Lumen)

Package comes with a built-in caching layer based on laravel cache.

```php
ES::search("foo")->remember(10)->get();
	
# you can specify a custom cache key

ES::search("foo")->remember(10, "last_documents")->get();
	
# Caching using other available driver
	
ES::search("foo")->cacheDriver("redis")->remember(10, "last_documents")->get();
	
# Caching with cache key prefix
	
ES::search("foo")->cacheDriver("redis")->cachePrefix("docs")->remember(10, "last_documents")->get();
```

##### Executing elasticsearch raw queries

```php
ES::raw()->search([
    "index" => "my_index",
    "type"  => "my_type",
    "body" => [
        "query" => [
            "bool" => [
                "must" => [
                    [ "match" => [ "address" => "mill" ] ],
                    [ "match" => [ "address" => "lane" ] ]
                ]
            ]
        ]
    ]
]);
```
   
##### Insert a new document
    
```php
ES::type("my_type")->id(3)->insert([
    "title" => "Test document",
    "content" => "Sample content"
]);
     
# A new document will be inserted with _id = 3.
  
# [id is optional] if not specified, a unique hash key will be generated.
```
  >
    
##### Bulk insert a multiple of documents at once.
     
```php
# Main query

ES::index("my_index")->type("my_type")->bulk(function ($bulk){

    # Sub queries

	$bulk->index("my_index_1")->type("my_type_1")->id(10)->insert(["title" => "Test document 1","content" => "Sample content 1"]);
	$bulk->index("my_index_2")->id(11)->insert(["title" => "Test document 2","content" => "Sample content 2"]);
	$bulk->id(12)->insert(["title" => "Test document 3", "content" => "Sample content 3"]);
	
});

# Notes from the above query:

# As index and type names are required for insertion, Index and type names are extendable. This means that: 

# So

# If index() is not specified in subquery:
# -- The builder will get index name in main query.
# -- if index is not specified in main query, the builder will get index name from configuration file.

# And

# If type() is not specified in subquery:
# -- The builder will get type name in main query.

# you can use old bulk code style using multidimensional array of [id => data] pairs
 
ES::type("my_type")->bulk([
 
	10 => [
		"title" => "Test document 1",
		"content" => "Sample content 1"
	],
	 
	11 => [
		"title" => "Test document 2",
		"content" => "Sample content 2"
	]
 
]);
 
# The two given documents will be inserted with its associated ids
```

##### Update an existing document
```php     
ES::type("my_type")->id(3)->update([
   "title" => "Test document",
   "content" => "sample content"
]);
    
# Document has _id = 3 will be updated.
    
# [id is required]
```
   
##### Incrementing field
```php
ES::type("my_type")->id(3)->increment("views");
    
# Document has _id = 3 will be incremented by 1.
    
ES::type("my_type")->id(3)->increment("views", 3);
    
# Document has _id = 3 will be incremented by 3.

# [id is required]
```
   
##### Decrementing field
```php 
ES::type("my_type")->id(3)->decrement("views");
    
# Document has _id = 3 will be decremented by 1.
    
ES::type("my_type")->id(3)->decrement("views", 3);
    
# Document has _id = 3 will be decremented by 3.

# [id is required]
```
   
##### Update using script
       
```php
# increment field by script
    
ES::type("my_type")->id(3)->script(
    "ctx._source.$field += params.count",
    ["count" => 1]
);
    
# add php tag to tags array list
    
ES::type("my_type")->id(3)->script(
    "ctx._source.tags.add(params.tag)",
    ["tag" => "php"]
);
    
# delete the doc if the tags field contain mongodb, otherwise it does nothing (noop)
    
ES::type("my_type")->id(3)->script(
    "if (ctx._source.tags.contains(params.tag)) { ctx.op = 'delete' } else { ctx.op = 'none' }",
    ["tag" => "mongodb"]
);
```
   
##### Delete a document
```php
ES::type("my_type")->id(3)->delete();
    
# Document has _id = 3 will be deleted.
    
# [id is required]
```

## Releases

  See [Change Log](https://github.com/basemkhirat/elasticsearch/blob/master/CHANGELOG.md).

## Author
[Basem Khirat](http://basemkhirat.com) - [basemkhirat@gmail.com](mailto:basemkhirat@gmail.com) - [@basemkhirat](https://twitter.com/basemkhirat)  


## Bugs, Suggestions and Contributions

Thanks to [everyone](https://github.com/basemkhirat/elasticsearch/graphs/contributors)
who has contributed to this project!

Please use [Github](https://github.com/basemkhirat/elasticsearch) for reporting bugs, 
and making comments or suggestions.

## License

MIT

`Have a happy searching..`
