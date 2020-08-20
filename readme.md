<p align="center">
<!-- <a href="https://travis-ci.org/basemkhirat/elasticsearch"><img src="https://travis-ci.org/basemkhirat/elasticsearch.svg?branch=master" alt="Build Status"></a> -->
<a href="https://packagist.org/packages/basemkhirat/elasticsearch"><img src="https://poser.pugx.org/basemkhirat/elasticsearch/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/basemkhirat/elasticsearch"><img src="https://poser.pugx.org/basemkhirat/elasticsearch/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/basemkhirat/elasticsearch"><img src="https://poser.pugx.org/basemkhirat/elasticsearch/license.svg" alt="License"></a>
</p>

## Laravel, Lumen and Native php elasticseach query builder to build complex queries using an elegant syntax

- Keeps you away from wasting your time by replacing array queries with a simple and elegant syntax you will love.
- Elasticsearch data model for types and indices inspired from laravel eloquent.
- Feeling free to create, drop, mapping and reindexing through easy artisan console commands.
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


## Documentation

See [Full Documentation](https://github.com/basemkhirat/elasticsearch/wiki/1.-Installation).

## Installation

### <u>Laravel Installation</u>


##### 1) Install package using composer.

```bash
$ composer require basemkhirat/elasticsearch
```

##### 2) Add package service provider (< laravel 5.5).

```php
Basemkhirat\Elasticsearch\ElasticsearchServiceProvider::class
```

##### 3) Add package alias (< laravel 5.5).

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
        ],
    ],
    
	// Custom handlers
	// 'handler' => new MyCustomHandler(),

    'index' => 'my_index',
    
    'logging' => [
        'enabled'   => env('ELASTIC_LOGGING_ENABLED',false),
        'level'     => env('ELASTIC_LOGGING_LEVEL','all'),
        'location'  => env('ELASTIC_LOGGING_LOCATION',base_path('storage/logs/elasticsearch.log'))
    ],  
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
  
  - `config/scout.php` where you can use package as a laravel scout driver.

## Working with console environment (Laravel & Lumen)

With some artisan commands you can do some tasks such as creating or updating settings, mappings and aliases.

Note that all commands are running with `--connection=default` option, you can change it through the command.

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

4) Reindex data from `my_index` into `my_new_index` with command:

```bash
$ php artisan es:indices:reindex my_index my_new_index

# Control bulk size. Adjust it with your server.

$ php artisan es:indices:reindex my_index my_new_index --bulk-size=2000

# Control query scroll value.

$ php artisan es:indices:reindex my_index my_new_index --bulk-size=2000 --scroll=2m

# Skip reindexing errors such as mapper parsing exceptions.

$ php artisan es:indices:reindex my_index my_new_index --bulk-size=2000 --skip-errors 

# Hide all reindexing errors and show the progres bar only.

$ php artisan es:indices:reindex my_index my_new_index --bulk-size=2000 --skip-errors --hide-errors
```

5) Remove `my_index_alias` alias from `my_index` and add it to `my_new_index` in configuration file and update with command:

```bash
$ php artisan es:indices:update
```


## Usage as a Laravel Scout driver

First, follow [Laravel Scout installation](https://laravel.com/docs/5.4/scout#installation).

All you have to do is updating these lines in `config/scout.php` configuration file.

```php
# change the default driver to 'es'
	
'driver' => env('SCOUT_DRIVER', 'es'),
	
# link `es` driver with default elasticsearch connection in config/es.php
	
'es' => [
    'connection' => env('ELASTIC_CONNECTION', 'default'),
],
```

Have a look at [laravel Scout documentation](https://laravel.com/docs/5.4/scout#configuration).



## Elasticsearch data model

Each index type has a corresponding "Model" which is used to interact with that type.
Models allow you to query for data in your types or indices, as well as insert new documents into the type.


##### Basic usage
```php
<?php

namespace App;

use Basemkhirat\Elasticsearch\Model;

class Post extends Model
{
        
    protected $type = "posts";
    
}
```

The above example will use the default connection and default index in `es.php`. You can override both in the next example.

```php
<?php

namespace App;

use Basemkhirat\Elasticsearch\Model;

class Post extends Model
{
    
    # [optional] Default: default elasticsearch driver
    # To override default conenction name of es.php file.
    # Assumed that there is a connection with name 'my_connection'
    protected $connection = "my_connection";
    
    # [optional] Default: default connection index
    # To override default index name of es.php file.
    protected $index = "my_index";
    
    protected $type = "posts";
    
}
```

##### Retrieving Models

Once you have created a model and its associated index type, you are ready to start retrieving data from your index. For example:


```php
<?php

use App\Post;

$posts = App\Post::all();

foreach ($posts as $post) {
    echo $post->title;
}

```

##### Adding Additional Constraints

The `all` method will return all of the results in the model's type. Each elasticsearch model serves as a query builder, you may also add constraints to queries, and then use the `get()` method to retrieve the results:

```php
$posts = App\Post::where('status', 1)
               ->orderBy('created_at', 'desc')
               ->take(10)
               ->get();

```


##### Retrieving Single Models

```php
// Retrieve a model by document key...
$posts = App\Post::find("AVp_tCaAoV7YQD3Esfmp");
```


##### Inserting Models


To create a new document, simply create a new model instance, set attributes on the model, then call the `save()` method:

```php
<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    /**
     * Create a new post instance.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        // Validate the request...

        $post = new Post;

        $post->title = $request->title;

        $post->save();
    }
}
```

##### Updating Models

The `save()` method may also be used to update models that already exist. To update a model, you should retrieve it, set any attributes you wish to update, and then call the save method.

```php
$post = App\Post::find(1);

$post->title = 'New Post Title';

$post->save();
```

##### Deleting Models

To delete a model, call the `delete()` method on a model instance:

```php
$post = App\Post::find(1);

$post->delete();
```

##### Query Scopes

Scopes allow you to define common sets of constraints that you may easily re-use throughout your application. For example, you may need to frequently retrieve all posts that are considered "popular". To define a scope, simply prefix an Eloquent model method with scope.

Scopes should always return a Query instance.

```php
<?php

namespace App;

use Basemkhirat\Elasticsearch\Model;

class Post extends Model
{
    /**
     * Scope a query to only include popular posts.
     *
     * @param \Basemkhirat\Elasticsearch\Query $query
     * @return \Basemkhirat\Elasticsearch\Query
     */
    public function scopePopular($query, $votes)
    {
        return $query->where('votes', '>', $votes);
    }

    /**
     * Scope a query to only include active posts.
     *
     * @param \Basemkhirat\Elasticsearch\Query $query
     * @return \Basemkhirat\Elasticsearch\Query
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
```

Once the scope has been defined, you may call the scope methods when querying the model. However, you do not need to include the scope prefix when calling the method. You can even chain calls to various scopes, for example:

```php
$posts = App\Post::popular(100)->active()->orderBy('created_at')->get();
```


##### Accessors & Mutators

###### Defining An Accessor
To define an `accessor`, create a getFooAttribute method on your model where `Foo` is the "studly" cased name of the column you wish to access. In this example, we'll define an accessor for the `title` attribute. The accessor will automatically be called by model when attempting to retrieve the value of the `title` attribute:


```php
<?php

namespace App;

use Basemkhirat\Elasticsearch\Model;

class post extends Model
{
    /**
     * Get the post title.
     *
     * @param  string  $value
     * @return string
     */
    public function getTitleAttribute($value)
    {
        return ucfirst($value);
    }
}
```

As you can see, the original value of the column is passed to the accessor, allowing you to manipulate and return the value. To access the value of the accessor, you may simply access the `title` attribute on a model instance:

```php
$post = App\Post::find(1);

$title = $post->title;
```

Occasionally, you may need to add array attributes that do not have a corresponding field in your index. To do so, simply define an accessor for the value:

```php
public function getIsPublishedAttribute()
{
    return $this->attributes['status'] == 1;
}
```

Once you have created the accessor, just add the value to the `appends` property on the model:

```php
protected $appends = ['is_published'];
```

Once the attribute has been added to the appends list, it will be included in model's array.

###### Defining A Mutator

To define a mutator, define a `setFooAttribute` method on your model where `Foo` is the "studly" cased name of the column you wish to access. So, again, let's define a mutator for the `title` attribute. This mutator will be automatically called when we attempt to set the value of the `title`attribute on the model:

```php
<?php

namespace App;

use Basemkhirat\Elasticsearch\Model;

class post extends Model
{
    /**
     * Set the post title.
     *
     * @param  string  $value
     * @return void
     */
    public function setTitleAttribute($value)
    {
        return strtolower($value);
    }
}
```

The mutator will receive the value that is being set on the attribute, allowing you to manipulate the value and set the manipulated value on the model's internal `$attributes` property. So, for example, if we attempt to set the title attribute to `Awesome post to read`:

```php
$post = App\Post::find(1);

$post->title = 'Awesome post to read';
```

In this example, the setTitleAttribute function will be called with the value `Awesome post to read`. The mutator will then apply the strtolower function to the name and set its resulting value in the internal $attributes array.



##### Attribute Casting


The `$casts` property on your model provides a convenient method of converting attributes to common data types. The `$casts` property should be an array where the key is the name of the attribute being cast and the value is the type you wish to cast the column to. The supported cast types are: `integer`, `float`, `double`, `string`, `boolean`, `object` and `array`.


For example, let's cast the `is_published` attribute, which is stored in our index as an integer (0 or  1) to a `boolean` value:

```php
<?php

namespace App;

use Basemkhirat\Elasticsearch\Model;

class Post extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_published' => 'boolean',
    ];
}

```

Now the `is_published` attribute will always be cast to a `boolean` when you access it, even if the underlying value is stored in the index as an integer:


```php
$post = App\Post::find(1);

if ($post->is_published) {
    //
}
```



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
ES::type("my_type")->id(3)->first();
    
# or
    
ES::type("my_type")->_id(3)->first();
```
##### Sorting
```php 
ES::type("my_type")->orderBy("created_at", "desc")->get();
    
# Sorting with text search score
    
ES::type("my_type")->orderBy("_score")->get();
```
##### Limit and offset
```php
ES::type("my_type")->take(10)->skip(5)->get();
```
##### Select only specific fields
```php    
ES::type("my_type")->select("title", "content")->take(10)->skip(5)->get();
```
##### Where clause
```php    
ES::type("my_type")->where("status", "published")->get();

# or

ES::type("my_type")->where("status", "=", "published")->get();
```
##### Where greater than
```php
ES::type("my_type")->where("views", ">", 150)->get();
```
##### Where greater than or equal
```php
ES::type("my_type")->where("views", ">=", 150)->get();
```
##### Where less than
```php
ES::type("my_type")->where("views", "<", 150)->get();
```
##### Where less than or equal
```php
ES::type("my_type")->where("views", "<=", 150)->get();
```
##### Where like
```php
ES::type("my_type")->where("title", "like", "foo")->get();
```
##### Where field exists
```php
ES::type("my_type")->where("hobbies", "exists", true)->get(); 

# or 

ES::type("my_type")->whereExists("hobbies", true)->get();
```    
##### Where in clause
```php    
ES::type("my_type")->whereIn("id", [100, 150])->get();
```
##### Where between clause 
```php    
ES::type("my_type")->whereBetween("id", 100, 150)->get();

# or 

ES::type("my_type")->whereBetween("id", [100, 150])->get();
```    
##### Where not clause
```php    
ES::type("my_type")->whereNot("status", "published")->get(); 

# or

ES::type("my_type")->whereNot("status", "=", "published")->get();
```
##### Where not greater than
```php
ES::type("my_type")->whereNot("views", ">", 150)->get();
```
##### Where not greater than or equal
```php
ES::type("my_type")->whereNot("views", ">=", 150)->get();
```
##### Where not less than
```php
ES::type("my_type")->whereNot("views", "<", 150)->get();
```
##### Where not less than or equal
```php
ES::type("my_type")->whereNot("views", "<=", 150)->get();
```
##### Where not like
```php
ES::type("my_type")->whereNot("title", "like", "foo")->get();
```
##### Where not field exists
```php
ES::type("my_type")->whereNot("hobbies", "exists", true)->get(); 

# or

ES::type("my_type")->whereExists("hobbies", true)->get();
```    
##### Where not in clause
```php    
ES::type("my_type")->whereNotIn("id", [100, 150])->get();
```
##### Where not between clause 
```php    
ES::type("my_type")->whereNotBetween("id", 100, 150)->get();

# or

ES::type("my_type")->whereNotBetween("id", [100, 150])->get();
```
   
##### Search by a distance from a geo point 
```php  
ES::type("my_type")->distance("location", ["lat" => -33.8688197, "lon" => 151.20929550000005], "10km")->get();

# or

ES::type("my_type")->distance("location", "-33.8688197,151.20929550000005", "10km")->get();

# or

ES::type("my_type")->distance("location", [151.20929550000005, -33.8688197], "10km")->get();  
```
  
  
##### Search using array queries
      
```php
ES::type("my_type")->body([
    "query" => [
         "bool" => [
             "must" => [
                 [ "match" => [ "address" => "mill" ] ],
                 [ "match" => [ "address" => "lane" ] ]
             ]
         ]
     ]
])->get();

# Note that you can mix between query builder and array queries.
# The query builder will will be merged with the array query.

ES::type("my_type")->body([

	"_source" => ["content"]
	
	"query" => [
	     "bool" => [
	         "must" => [
	             [ "match" => [ "address" => "mill" ] ]
	         ]
	     ]
	],
	   
	"sort" => [
		"_score"
	]
     
])->select("name")->orderBy("created_at", "desc")->take(10)->skip(5)->get();

# The result query will be
/*
Array
(
    [index] => my_index
    [type] => my_type
    [body] => Array
        (
            [_source] => Array
                (
                    [0] => content
                    [1] => name
                )
            [query] => Array
                (
                    [bool] => Array
                        (
                            [must] => Array
                                (
                                    [0] => Array
                                        (
                                            [match] => Array
                                                (
                                                    [address] => mill
                                                )
                                        )
                                )
                        )
                )
            [sort] => Array
                (
                    [0] => _score
                    [1] => Array
                        (
                            [created_at] => desc
                        )
                )
        )
    [from] => 5
    [size] => 10
    [client] => Array
        (
            [ignore] => Array
                (
                )
        )
)
*/

```
  
##### Search the entire document
    
```php
ES::type("my_type")->search("hello")->get();
    
# search with Boost = 2
    
ES::type("my_type")->search("hello", 2)->get();

# search within specific fields with different weights

ES::type("my_type")->search("hello", function($search){
	$search->boost(2)->fields(["title" => 2, "content" => 1])
})->get();
```

##### Search with highlight fields
    
```php
$doc = ES::type("my_type")->highlight("title")->search("hello")->first();

# Multiple fields Highlighting is allowed.

$doc = ES::type("my_type")->highlight("title", "content")->search("hello")->first();

# Return all highlights as array using $doc->getHighlights() method.

$doc->getHighlights();

# Also you can return only highlights of specific field.

$doc->getHighlights("title");
```


##### Return only first record

```php    
ES::type("my_type")->search("hello")->first();
```
  
##### Return only count
```php    
ES::type("my_type")->search("hello")->count();
```
    
##### Scan-and-Scroll queries
    


```php
# These queries are suitable for large amount of data. 
# A scrolled search allows you to do an initial search and to keep pulling batches of results
# from Elasticsearch until there are no more results left.
# It’s a bit like a cursor in a traditional database
    
$documents = ES::type("my_type")->search("hello")
                 ->scroll("2m")
                 ->take(1000)
                 ->get();

# Response will contain a hashed code `scroll_id` will be used to get the next result by running

$documents = ES::type("my_type")->search("hello")
                 ->scroll("2m")
                 ->scrollID("DnF1ZXJ5VGhlbkZldGNoBQAAAAAAAAFMFlJQOEtTdnJIUklhcU1FX2VqS0EwZncAAAAAAAABSxZSUDhLU3ZySFJJYXFNRV9laktBMGZ3AAAAAAAAAU4WUlA4S1N2ckhSSWFxTUVfZWpLQTBmdwAAAAAAAAFPFlJQOEtTdnJIUklhcU1FX2VqS0EwZncAAAAAAAABTRZSUDhLU3ZySFJJYXFNRV9laktBMGZ3")
                 ->get();

# And so on ...
# Note that you don't need to write the query parameters in every scroll. All you need the `scroll_id` and query scroll time.
    
# To clear `scroll_id` 
  
ES::type("my_type")->scrollID("DnF1ZXJ5VGhlbkZldGNoBQAAAAAAAAFMFlJQOEtTdnJIUklhcU1FX2VqS0EwZncAAAAAAAABSxZSUDhLU3ZySFJJYXFNRV9laktBMGZ3AAAAAAAAAU4WUlA4S1N2ckhSSWFxTUVfZWpLQTBmdwAAAAAAAAFPFlJQOEtTdnJIUklhcU1FX2VqS0EwZncAAAAAAAABTRZSUDhLU3ZySFJJYXFNRV9laktBMGZ3")
        ->clear();
```
    
##### Paginate results with 5 records per page

```php   
$documents = ES::type("my_type")->search("hello")->paginate(5);
    
# Getting pagination links
    
$documents->links();

# Bootstrap 4 pagination

$documents->links("bootstrap-4");

# Simple bootstrap 4 pagination

$documents->links("simple-bootstrap-4");

# Simple pagination

$documents->links("simple-default");
```

These are all pagination methods you may use:

```php
$documents->count()
$documents->currentPage()
$documents->firstItem()
$documents->hasMorePages()
$documents->lastItem()
$documents->lastPage()
$documents->nextPageUrl()
$documents->perPage()
$documents->previousPageUrl()
$documents->total()
$documents->url($page)
```

##### Getting the query array without execution

```php
ES::type("my_type")->search("hello")->where("views", ">", 150)->query();
```

##### Getting the original elasticsearch response

```php
ES::type("my_type")->search("hello")->where("views", ">", 150)->response();
```

##### Ignoring bad HTTP response

```php      
ES::type("my_type")->ignore(404, 500)->id(5)->first();
```

##### Query Caching (Laravel & Lumen)

Package comes with a built-in caching layer based on laravel cache.

```php
ES::type("my_type")->search("hello")->remember(10)->get();
	
# Specify a custom cache key

ES::type("my_type")->search("hello")->remember(10, "last_documents")->get();
	
# Caching using other available driver
	
ES::type("my_type")->search("hello")->cacheDriver("redis")->remember(10, "last_documents")->get();
	
# Caching with cache key prefix
	
ES::type("my_type")->search("hello")->cacheDriver("redis")->cachePrefix("docs")->remember(10, "last_documents")->get();
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

# If index() is not specified in subquery:
# -- The builder will get index name from the main query.
# -- if index is not specified in main query, the builder will get index name from configuration file.

# And

# If type() is not specified in subquery:
# -- The builder will get type name from the main query.

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

```php
# Bulk update

ES::type("my_type")->bulk(function ($bulk){
    $bulk->id(10)->update(["title" => "Test document 1","content" => "Sample content 1"]);
    $bulk->id(11)->update(["title" => "Test document 2","content" => "Sample content 2"]);
});
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

```php
# Bulk delete

ES::type("my_type")->bulk(function ($bulk){
    $bulk->id(10)->delete();
    $bulk->id(11)->delete();
});
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
