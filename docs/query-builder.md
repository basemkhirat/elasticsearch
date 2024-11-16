# Query Builder

The query builder provides an elegant way to build Elasticsearch queries. Here's how to use it:


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

## Regular Expression Queries

You can use regular expressions in your queries using the `regex` operator with `where` and `whereNot` methods. This allows for powerful pattern matching in your searches.

### Basic Usage

```php
// Match titles containing text within parentheses
$results = ES::where("title", "regex", ".*\\(.*\\).*")->get();

// Match emails from specific domain
$results = ES::where("email", "regex", ".*@gmail\\.com")->get();

// Exclude URLs that don't start with https
$results = ES::whereNot("url", "regex", "^https://.*")->get();
```

### Common Patterns

1. Text Patterns:
```php
// Starts with
ES::where("field", "regex", "^start.*");

// Ends with
ES::where("field", "regex", ".*end$");

// Contains
ES::where("field", "regex", ".*contains.*");
```

2. Number Patterns:
```php
// Phone numbers (e.g., 123-456-7890)
ES::where("phone", "regex", "[0-9]{3}-[0-9]{3}-[0-9]{4}");

// Years between 1900-2099
ES::where("year", "regex", "(19|20)[0-9]{2}");
```

3. Special Characters:
```php
// Text within parentheses
ES::where("field", "regex", ".*\\(.*\\).*");

// Text within brackets
ES::where("field", "regex", ".*\\[.*\\].*");
```

### Important Notes

1. Elasticsearch uses Lucene's regex syntax which:
   - Does not support backreferences
   - Does not support lookahead/lookbehind
   - Requires escaping special characters with backslashes

2. Performance Tips:
   - Prefix patterns (starting with ^) are more efficient
   - Avoid patterns starting with wildcards when possible
   - Use more specific patterns for better performance

3. Best Practices:
   - Test patterns with small datasets first
   - Consider using term/match queries for simple exact matches
   - Combine with other query methods for more precise results

### Examples with Other Query Methods

```php
$results = ES::index('books')
    ->where("title", "regex", "^The.*")
    ->where("year", ">", 2000)
    ->whereNot("category", "regex", "spam|adult")
    ->take(10)
    ->get();
```
