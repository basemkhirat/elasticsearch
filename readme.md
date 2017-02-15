<p align="center">

<a href="https://travis-ci.org/basemkhirat/elasticsearch"><img src="https://travis-ci.org/basemkhirat/elasticsearch.svg" alt="Build Status"></a>

<a href="https://packagist.org/packages/basemkhirat/elasticsearch"><img src="https://poser.pugx.org/basemkhirat/elasticsearch/v/stable.svg" alt="Latest Stable Version"></a>

<a href="https://packagist.org/packages/basemkhirat/elasticsearch"><img src="https://poser.pugx.org/basemkhirat/elasticsearch/d/total.svg" alt="Total Downloads"></a>

<a href="https://packagist.org/packages/basemkhirat/elasticsearch"><img src="https://poser.pugx.org/basemkhirat/elasticsearch/license.svg" alt="License"></a>

</p>

<p align="center"><img src="http://basemkhirat.com/images/basemkhirat-elasticsearch.png"></p>


## Laravel elasticseach query builder to build complex queries using an elegant syntax

- Keep away from wasting your time by replacing array queries with a simple and elegant syntax you will love.
- Supports [laravel 5.4](https://laravel.com/docs/5.4) and can be used as a  [laravel scout](https://laravel.com/docs/5.4/scout) driver.
- Dealing with multiple elasticsearch connections at the same time.
- Supports scan and scroll queries for dealing big data.
- Awesome pagination based on [LengthAwarePagination](https://github.com/illuminate/pagination).
- Feeling free to create, drop and mapping index fields.
- Caching queries using a caching layer over query builder built on [laravel cache](https://laravel.com/docs/5.4/cache).

## Requirements

- php >= 5.6.6 
  
  See [Travis CI Builds](https://travis-ci.org/basemkhirat/elasticsearch).

- laravel/laravel >= 5.3

## Installation

##### 1) Install package using composer:

	composer require basemkhirat/elasticsearch

##### 2) Add package service provider:

	Basemkhirat\Elasticsearch\ElasticsearchServiceProvider::class
	
##### 3) Add package alias:

	'ES' => Basemkhirat\Elasticsearch\Facades\ES::class
	
##### 4) Publishing:
    
    php artisan vendor:publish

	
## Configuration

  
  After publishing, two configuration files will be created.
  
  - `config/es.php` where you can add more than one elasticsearch server.

```

 'default' => env('ELASTIC_CONNECTION', 'default'),

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
        'index' => env('ELASTIC_INDEX', 'my_index'),
        'type' => env('ELASTIC_TYPE', 'my_type'),
    ]
]

```
  
  - `config/scout.php` where you can use package as a laravel scout driver.


All you have to do is updating these lines in `config/scout.php` configuration file.

	
	# change the default driver to `es`
	
	'driver' => env('SCOUT_DRIVER', 'es'),
	
	# link `es` driver with default elasticsearch connection in config/es.php
	
	'es' => [
        'connection' => env('ELASTIC_CONNECTION', 'default'),
    ],

Have a look at [laravel Scout documentation](https://laravel.com/docs/5.4/scout#configuration).

## Usage

#### Creating a new index

    ES::create("my_index");
    
    # or 
    
    ES::index("my_index")->create();
    
    
##### Creating index with custom options (optional)
    
    ES::index("my_index")->create(function($index){
            
        $index->shards(5)->replicas(1)->mappping([
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
      
          $index->shards(5)->replicas(1)->mappping([
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


#### Dropping index

    ES::drop("my_index");
        
    # or
    
    ES::index("my_index")->drop();
    
#### Running queries

    $documents = ES::connection("default")
                    ->index("my_index")
                    ->type("my_type")
                    ->get();    # return collection of results

You can rewrite the above query to

    $documents = ES::get();    # return collection of results
    
The query builder will use the default connection, index, and type names setted in configuration file `es.php`. 
 
Index and type names setted in query overrides their values in `es.php`.


---

##### Getting document by id

    $documents = ES::id(3)->get();
    
    # or
    
    $documents = ES::_id(3)->get();

##### Sorting
    
    $documents = ES::orderBy("created_at", "desc")->get();
    
    # Sorting with text search score
    
    $documents = ES::orderBy("_score")->get();
    
##### Limit and offset
    
    $documents = ES::take(10)->skip(5)->get();
    
##### Select only specific fields
    
    $documents = ES::select("title", "content")->take(10)->skip(5)->get();
    
##### Where clause
    
    ES::where("views", 150)->get(); or ES::where("views", "=", 150)->get();

##### Where greater than

    ES::where("views", ">", 150)->get();
    
##### Where greater than or equal

    ES::where("views", ">=", 150)->get();
    
##### Where less than

    ES::where("views", "<", 150)->get();
    
##### Where greater than or equal

    ES::where("views", "<=", 150)->get();
    
##### Where like

    ES::where("title", "like", "foo")->get();
    
##### Where field exists

    ES::where("hobbies", "exists", true)->get(); or ES::whereExists("hobbies", true)->get();
    
##### Where in clause
    
    ES::whereIn("id", [100, 150])->get();
    
##### Where between clause 
    
    ES::whereBetween("id", 100, 150)->get();
   
  >
    
##### Where not clause
    
    ES::whereNot("views", 150)->get(); or ES::where("views", "=", 150)->get();

##### Where not greater than

    ES::whereNot("views", ">", 150)->get();

##### Where not greater than or equal

    ES::whereNot("views", ">=", 150)->get();
    
##### Where not less than

    ES::whereNot("views", "<", 150)->get();
    
##### Where not less than or equal

    ES::whereNot("views", "<=", 150)->get();
    
##### Where not like

    ES::whereNot("title", "like", "foo")->get();
    
##### Where not field exists

    ES::whereNot("hobbies", "exists", true)->get(); or ES::whereExists("hobbies", true)->get();
    
##### Where not in clause
    
    ES::whereNotIn("id", [100, 150])->get();
    
##### Where not between clause 
    
    ES::whereNotBetween("id", 100, 150)->get();
    
    
  >
  
##### Search the entire document
    
    
    ES::search("bar")->get();
    
    # search with Boost = 2
    
    ES::search("bar", 2)->get();
    
  >
  
##### Return only first record
    
    ES::search("bar")->first();
    
  >
  
##### Return only count
    
    ES::search("bar")->count();
    
    
##### Scan-and-Scroll queries
    
 These queries are suitable for large amount of data. 
    A scrolled search allows you to do an initial search and to keep pulling batches of results
    from Elasticsearch until there are no more results left. It’s a bit like a cursor in a traditional database
    
    $documents = ES::search("foo")
                    ->scroll("2m")
                    ->take(1000)
                    ->get();
                    
  Response will contain a hashed code `scroll_id` will be used to get the next result by running
    
    $documents = ES::search("my_index")
                        ->scroll("2m")
                        ->scrollID("DnF1ZXJ5VGhlbkZldGNoBQAAAAAAAAFMFlJQOEtTdnJIUklhcU1FX2VqS0EwZncAAAAAAAABSxZSUDhLU3ZySFJJYXFNRV9laktBMGZ3AAAAAAAAAU4WUlA4S1N2ckhSSWFxTUVfZWpLQTBmdwAAAAAAAAFPFlJQOEtTdnJIUklhcU1FX2VqS0EwZncAAAAAAAABTRZSUDhLU3ZySFJJYXFNRV9laktBMGZ3")
                        ->get();
                        
   And so on ...
    
   Note that you don't need to write the query parameters in every scroll.
    All you need the `scroll_id` and query scroll time.
    
   To clear `scroll_id` 
    
    ES::scrollID("DnF1ZXJ5VGhlbkZldGNoBQAAAAAAAAFMFlJQOEtTdnJIUklhcU1FX2VqS0EwZncAAAAAAAABSxZSUDhLU3ZySFJJYXFNRV9laktBMGZ3AAAAAAAAAU4WUlA4S1N2ckhSSWFxTUVfZWpLQTBmdwAAAAAAAAFPFlJQOEtTdnJIUklhcU1FX2VqS0EwZncAAAAAAAABTRZSUDhLU3ZySFJJYXFNRV9laktBMGZ3")
        ->clear();
    
  >
    
##### Paginate results with per_page = 5
      
    $documents = ES::search("bar")->paginate(5);
    
    # getting pagination links
    
    $documents->links();
    
    
  >
  
##### Getting the query array without execution

	$query = ES::search("foo")->where("views", ">", 150)->query();
  
##### Ignoring bad HTTP response
      
    $documents = ES::ignore(404, 500)->id(5)->first();
    
    
  >
  
  
##### Query Caching 

Package comes with a built-in caching layer based on laravel cache.

	ES::search("foo")->remember(10)->get();
	
	# you can specify a custom cache key

	ES::search("foo")->remember(10, "last_documents")->get();
	
	# Caching using other available driver
	
	ES::search("foo")->cacheDriver("redis")->remember(10, "last_documents")->get();
	
	# Caching with cache key prefix
	
	ES::search("foo")->cacheDriver("redis")->cachePrefix("docs")->remember(10, "last_documents")->get();
	
   

##### Executing elasticsearch raw queries
    
    ES::raw()->search([
        "index" => "my_index",
        "type"  => "my_type",
        "body"  => [
            "query": [
            "bool": [
                    "must": [
                        [ "match": [ "address": "mill" ] ],
                        [ "match": [ "address": "lane" ] ] 
                    ]
                ]
            ]
        ]
    ]);
  
  
   >
   
##### Insert a new document
    
    ES::id(3)->insert([
        "title" => "Test document",
        "content" => "Sample content"
    ]);
    
    
    A new document will be inserted with _id = 3.
  
    [id is optional] if not specified, a unique hash key will be generated 

  
  >
    
##### Bulk insert a multiple of documents at once using multidimensional array of [id => data] pairs
     
     ES::bulk(
         10 => [
            "title" => "Test document 1",
            "content" => "Sample content 1"
         ],
         11 => [
            "title" => "Test document 2",
            "content" => "Sample content 2"
         ],
     );
     
     The two given documents will be inserted with its associated ids
  
   >
   
##### Update an existing document
       
    ES::id(3)->update([
       "title" => "Test document",
       "content" => "sample content"
    ]);
        
    Document has _id = 3 will be updated.
    
    [id is required]
    
   >
   
##### Incrementing field
       
    ES::id(3)->increment("views");
        
    Document has _id = 3 will be incremented by 1.
    
    ES::id(3)->increment("views", 3);
    
    Document has _id = 3 will be incremented by 3.

    [id is required]
    
   >
   
##### Decrementing field
       
    ES::id(3)->decrement("views");
        
    Document has _id = 3 will be decremented by 1.
    
    ES::id(3)->decrement("views", 3);
    
    Document has _id = 3 will be decremented by 3.

    [id is required]
    
   >
   
##### Update using script
       

    # increment field by script
    
    ES::id(3)->script(
        "ctx._source.$field += params.count",
        ["count" => 1]
    );
    
    # add php tag to tags array list
    
    ES::id(3)->script(
        "ctx._source.tags.add(params.tag)",
        ["tag" => "php"]
    );
    
    # delete the doc if the tags field contain mongodb, otherwise it does nothing (noop)
    
    ES::id(3)->script(
        "if (ctx._source.tags.contains(params.tag)) { ctx.op = 'delete' } else { ctx.op = 'none' }",
        ["tag" => "mongodb"]
    );
    
   >
   
##### Delete a document
       
    ES::id(3)->delete();
        
    Document has _id = 3 will be deleted.
    
    [id is required]
    
### Author
[Basem Khirat](http://basemkhirat.com) - [basemkhirat@gmail.com](mailto:basemkhirat@gmail.com) - [@basemkhirat](https://twitter.com/basemkhirat)  


### Bugs, Suggestions and Contributions

Thanks to [everyone](https://github.com/basemkhirat/elasticsearch/graphs/contributors)
who has contributed to this project!

Please use [Github](https://github.com/basemkhirat/elasticsearch) for reporting bugs, 
and making comments or suggestions.

### License

MIT

`Have a happy searching.. `
