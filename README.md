[![Latest Stable Version](https://poser.pugx.org/matchory/elasticsearch/v)](https://packagist.org/packages/matchory/elasticsearch) [![Total Downloads](https://poser.pugx.org/matchory/elasticsearch/downloads)](https://packagist.org/packages/matchory/elasticsearch) [![Latest Unstable Version](https://poser.pugx.org/matchory/elasticsearch/v/unstable)](https://packagist.org/packages/matchory/elasticsearch) [![License](https://poser.pugx.org/matchory/elasticsearch/license)](https://packagist.org/packages/matchory/elasticsearch)

Laravel Elasticsearch Integration
=================================
This is a fork of the excellent library by [@basemkhirat](https://github.com/basemkhirat), who sadly seems to have abandoned it by now.  
As we rely on this library quite heavily, we will attempt to keep it up to date and compatible with newer Laravel and Elasticsearch versions.

**Changes in this fork:**
- [x] Support for Elasticsearch 7.10 and newer
- [x] Support for PHP 7.3 and newer (PHP 8 included!)
- [x] Broadened support for Laravel libraries, allowing you to use it with almost all versions of Laravel
- [x] Type hints in all supported places, giving confidence in all parameters
- [x] Docblock annotations for advanced autocompletion, extensive inline documentation
- [x] Clean separation of connection management into a [`ConnectionManager` class](./src/ConnectionManager.php), while preserving backwards compatibility
- [x] Support for _most_ Eloquent model behaviour ([see below](#elasticsearch-models))
- [x] Removed dependencies on Laravel internals

If you're interested in contributing, please submit a PR or open an issue!

**Features:**
- Fluent Elasticsearch query builder with an elegant syntax
- Elasticsearch models inspired by Laravel's Eloquent
- Index management using simple artisan commands
- Limited support for the [Lumen framework](https://lumen.laravel.com/)
- Can be used as a [Laravel Scout](https://laravel.com/docs/8.x/scout) driver
- Parallel usage of multiple Elasticsearch connections
- Built-in pagination based on [Laravel Pagination](https://laravel.com/docs/8.x/pagination)
- Caching queries using a caching layer based on [laravel cache](https://laravel.com/docs/8.x/cache).

**Table of Contents**
- [Requirements](#requirements)
- [Installation](#installation)
    * [Install package using composer](#install-package-using-composer)
        + [Laravel Installation](#laravel-installation)
        + [Lumen Installation](#lumen-installation)
    * [Generic app installation](#generic-app-installation)
- [Configuration (Laravel & Lumen)](#configuration-laravel-lumen)
- [Artisan commands (Laravel & Lumen)](#artisan-commands-laravel-lumen)
    * [`es:indices:list`: List all indices on server](#es-indices-list-list-all-indices-on-server)
    * [`es:indices:create`: Create indices defined in `config/es.php`](#es-indices-create-create-indices-defined-in-config-esphp)
    * [`es:indices:update`: Update indices defined in `config/es.php`](#es-indices-update-update-indices-defined-in--config-esphp)
    * [`es:indices:drop`: Drop index](#es-indices-drop-drop-index)
    * [Reindexing data (with zero downtime)](#reindexing-data-with-zero-downtime)
- [Usage as a Laravel Scout driver](#usage-as-a-laravel-scout-driver)
- [Elasticsearch models](#elasticsearch-models)
    * [Index Names](#index-names)
    * [Connection Names](#connection-names)
    * [Mapping type](#mapping-type)
    * [Default Attribute Values](#default-attribute-values)
    * [Retrieving Models](#retrieving-models)
    * [Adding additional constraints](#adding-additional-constraints)
    * [Collections](#collections)
    * [Chunking Results](#chunking-results)
    * [Retrieving individual Models](#retrieving-individual-models)
    * [Not Found Exceptions](#not-found-exceptions)
    * [Inserting and Updating Models](#inserting-and-updating-models)
        + [Inserts](#inserts)
        + [Updates](#updates)
        + [Examining Attribute Changes](#examining-attribute-changes)
        + [Mass Assignment](#mass-assignment)
        + [Allowing Mass Assignment](#allowing-mass-assignment)
        + [Upserts](#upserts)
        + [Deleting An Existing Model By Its ID](#deleting-an-existing-model-by-its-id)
    * [Query Scopes](#query-scopes)
        + [Global Scopes](#global-scopes)
        + [Local Scopes](#local-scopes)
        + [Dynamic Scopes](#dynamic-scopes)
    * [Comparing Models](#comparing-models)
    * [Events](#events)
    * [Replicating Models](#replicating-models)
    * [Mutators and Casting](#mutators-and-casting)
- [Usage as a query builder](#usage-as-a-query-builder)
- [Releases](#releases)
- [Authors](#authors)
- [Bugs, Suggestions and Contributions](#bugs-suggestions-and-contributions)
- [License](#license)

Requirements
------------
- PHP >= `7.3`  
  See [Travis CI Builds](https://travis-ci.org/matchory/elasticsearch).
- `laravel/laravel` >= 5.* or `laravel/lumen` >= 5.* or any other application using composer

Installation
------------
This section describes the installation process for all supported application types.

### Install package using composer
Whether you're using Laravel, Lumen or another framework, start by installing the package using composer:
```bash
composer require matchory/elasticsearch
```

#### Laravel Installation
If you have package autodiscovery disabled, add the service provider and facade to your `config/app.php`:
```php
    'providers' => [
        // ...

        Matchory\Elasticsearch\ElasticsearchServiceProvider::class,

        // ...
    ],

    // ...

    'aliases' => [
        // ...

        'ES' => Matchory\Elasticsearch\Facades\ES::class,

        // ...
    ],
```

Lastly, publish the service provider to your configuration directory:
```bash
php artisan vendor:publish --provider="Matchory\Elasticsearch\ElasticsearchServiceProvider"
```

#### Lumen Installation
After installing the package from composer, add package service provider in `bootstrap/app.php`:
```php
$app->register(Matchory\Elasticsearch\ElasticsearchServiceProvider::class);
```

Copy the package config directory at `vendor/matchory/elasticsearch/src/config/` to your project root folder alongside with your `app/` directory:
```bash
cp -r ./vendor/matchory/elasticsearch/src/config ./config
```

If you haven't already, make Lumen work with facades by uncommenting this line in `bootstrap/app.php`:
```php
$app->withFacades();
```

If you don't want to enable facades in Lumen, you can access the query builder using `app("es")`:
```php
app("es")->index("my_index")->type("my_type")->get();

# This is similar to:
ES::index("my_index")->type("my_type")->get();
```   

### Generic app installation
You can install package with any composer-based application. While we can't provide general instructions, the following example should give you an idea of how
it works:
```php
require "vendor/autoload.php";

use Matchory\Elasticsearch\ConnectionManager;
use Matchory\Elasticsearch\Factories\ClientFactory;

$connectionManager = new ConnectionManager([
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
], new ClientFactory());

$connection = $connectionManager->connection();

// Access the query builder using created connection
$documents = $connection->search("hello")->get();
```

Configuration (Laravel & Lumen)
-------------------------------
After publishing the service provider, a configuration file has been created at `config/es.php`. Here, you can add one or more Elasticsearch connections, with
multiple servers each. Take a look at the following example:
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

If you'd like to use Elasticsearch with [Laravel Scout](https://laravel.com/docs/8.x/scout#introduction), you can find the scout specific settings in
`config/scout.php`.

Artisan commands (Laravel & Lumen)
----------------------------------
With the artisan commands included with this package, you can create or update settings, mappings and aliases. Note that all commands use the default connection
by default. You can change this by passing the `--connection <your_connection_name>` option.

The following commands are available:

### `es:indices:list`: List all indices on server
```bash-
$ php artisan es:indices:list
+----------------------+--------+--------+----------+------------------------+-----+-----+------------+--------------+------------+----------------+
| configured (es.php)  | health | status | index    | uuid                   | pri | rep | docs.count | docs.deleted | store.size | pri.store.size |
+----------------------+--------+--------+----------+------------------------+-----+-----+------------+--------------+------------+----------------+
| yes                  | green  | open   | my_index | 5URW60KJQNionAJgL6Q2TQ | 1   | 0   | 0          | 0            | 260b       | 260b           |
+----------------------+--------+--------+----------+------------------------+-----+-----+------------+--------------+------------+----------------+
```

### `es:indices:create`: Create indices defined in `config/es.php`
Note that creating operation skips the index if exists.
```bash
# Create all indices in config file.
php artisan es:indices:create

# Create only 'my_index' index in config file
php artisan es:indices:create my_index 
```

### `es:indices:update`: Update indices defined in `config/es.php`
Note that updating operation updates indices setting, aliases and mapping and doesn't delete the indexed data.
```bash
# Update all indices in config file.
php artisan es:indices:update

# Update only 'my_index' index in config file
php artisan es:indices:update my_index 
```

### `es:indices:drop`: Drop index
**Be careful when using this command, as you will lose your index data!**  
Running drop command with `--force` option will skip all confirmation messages.
```bash
# Drop all indices in config file.
php artisan es:indices:drop

# Drop specific index on sever. Not matter for index to be exist in config file or not.
php artisan es:indices:drop my_index 
```

### Reindexing data (with zero downtime)

**First, why reindexing?**  
Changing index mapping doesn't reflect without data reindexing, otherwise your search results will not work on the right way.  
To avoid down time, your application should work with index `alias` not index `name`.  
The index `alias` is a constant name that application should work with to avoid change index names.

**Assume that we want to change mapping for `my_index`, this is how to do that:**
1. Add `alias` as example `my_index_alias` to `my_index` configuration and make sure your application is working with it.
   ```php
   "aliases" => [
       "my_index_alias"
   ]       
   ```

2. Update index with command:
   ```bash
   php artisan es:indices:update my_index
   ```

3. Create a new index as example `my_new_index` with your new mapping in configuration file.
   ```bash
   $ php artisan es:indices:create my_new_index
   ```

4. Reindex data from `my_index` into `my_new_index` with command:
   ```bash
   php artisan es:indices:reindex my_index my_new_index
   
   # Control bulk size. Adjust it with your server.
   php artisan es:indices:reindex my_index my_new_index --bulk-size=2000
   
   # Control query scroll value.
   php artisan es:indices:reindex my_index my_new_index --bulk-size=2000 --scroll=2m
   
   # Skip reindexing errors such as mapper parsing exceptions.
   php artisan es:indices:reindex my_index my_new_index --bulk-size=2000 --skip-errors 
   
   # Hide all reindexing errors and show the progres bar only.
   php artisan es:indices:reindex my_index my_new_index --bulk-size=2000 --skip-errors --hide-errors
   ```

5. Remove `my_index_alias` alias from `my_index` and add it to `my_new_index` in configuration file and update with command:
   ```bash
   php artisan es:indices:update
   ```

Usage as a Laravel Scout driver
-------------------------------
First, follow [Laravel Scout installation](https://laravel.com/docs/8.0/scout#installation).  
All you have to do is updating the following lines in `config/scout.php`:
```php
# change the default driver to 'es'
'driver' => env('SCOUT_DRIVER', 'es'),

# link `es` driver with default elasticsearch connection in config/es.php
'es' => [
    'connection' => env('ELASTIC_CONNECTION', 'default'),
],
```

Have a look at [Laravel Scout documentation](https://laravel.com/docs/8.0/scout#configuration), too!

Elasticsearch models
--------------------
Each index type has a corresponding _"Model"_ which is used to interact with that type. Models allow you to query for data in your types or indices, as well as
insert new documents into the type. Elasticsearch Models mimic Eloquent models as closely as possible: You can use model events, route bindings, advanced
attribute methods and more. **If there is any Eloquent functionality you're missing, open an issue, and we'll be happy to add it!**.

> **Supported features:**
>  - Attributes
>  - Events
>  - Route bindings
>  - Global and Local Query Scopes
>  - Replicating models

A minimal model might look like this:
```php
namespace App\Models;

use Matchory\Elasticsearch\Model;

class Post extends Model
{
    // ...
}
```

### Index Names
This model is not specifically bound to any index and will simply use the index configured for the given Elasticsearch connection. To specifically target an
index, you may define an `index` property on the model:

```php
namespace App\Models;

use Matchory\Elasticsearch\Model;

class Post extends Model
{
    protected $index = 'posts';
}
```

### Connection Names
By default, all Elasticsearch models will use the default connection that's configured for your application. If you would like to specify a different connection
that should be used when interacting with a particular model, you should define a $connection property on the model:

```php
namespace App\Models;

use Matchory\Elasticsearch\Model;

class Post extends Model
{
    protected $connection = 'blag';
}
```

### Mapping type
If you're still using mapping types, you may add a `type` property to your model to indicate the mapping `_type` to be used for queries.

> **Mapping Types are deprecated:**  
> Please note that Elastic has [deprecated mapping types](https://www.elastic.co/guide/en/elasticsearch/reference/current/removal-of-types.html) and will remove
> them in the next major release. You should not rely on them to continue working.

```php
namespace App;

use Matchory\Elasticsearch\Model;

class Post extends Model
{
    protected $type = 'posts';
}
```

### Default Attribute Values
By default, a newly instantiated model instance will not contain any attribute values. If you would like to define the default values for some of your model's
attributes, you may define an `attributes` property on your model:

```php
namespace App\Models;

use Matchory\Elasticsearch\Model;

class Post extends Model
{
    protected $attributes = [
        'published' => false,
    ];
}
```

### Retrieving Models
Once you have created a model and its associated index type, you are ready to start retrieving data from your index. You can think of your Elasticsearch model
as a powerful query builder allowing you to fluently query the index associated with the model. The model's `all` method will retrieve all the documents from
the model's associated Elasticsearch index:

```php
use App\Models\Post;

foreach (Post::all() as $post) {
    echo $post->title;
}
```

### Adding additional constraints
The `all` method will return all the results in the model's index. However, since each Elasticsearch model serves as a query builder, you may add additional
constraints to queries, and then invoke the `get()` method to retrieve the results:

```php
use App\Models\Post;

$posts = Post::where('status', 1)
             ->orderBy('created_at', 'desc')
             ->take(10)
             ->get();
```

### Collections
As we have seen, Elasticsearch methods like `all` and `get` retrieve multiple documents from the index. However, these methods don't return a plain PHP array.
Instead, an instance of [`Matchory\Elasticsearch\Collection`](./src/Collection.php) is returned.

The Elasticsearch `Collection` class extends Laravel's base `Illuminate\Support\Collection` class, which provides a
[variety of helpful methods](https://laravel.com/docs/master/collections#available-methods) for interacting with data collections. For example, the `reject`
method may be used to remove models from a collection based on the results of an invoked closure:

```php
use App\Models\Post;

$posts = Post::where('sponsored', true)->get();
$posts = $posts->reject($post => $post->in_review);
```

In addition to the methods provided by Laravel's base collection class, the Elasticsearch collection class provides a few extra methods that are specifically
intended for interacting with collections of Elasticsearch models:

#### Result Meta data
Elasticsearch provides a few additional fields in addition to the hits of a query, like the total result amount, or the query execution time. The Elasticsearch
collection provides getters for these properties:
```php
use App\Models\Post;

$posts = Post::all();
$total = $posts->getTotal();
$maxScore = $posts->getMaxScore();
$duration = $posts->getDuration();
$isTimedOut = $posts->isTimedOut();
$scrollId = $posts->getScrollId();
$shards = $posts->getShards();
```

#### Iterating
Since all of Laravel's collections implement PHP's `iterable` interfaces, you may loop over collections as if they were an array:

```php
foreach ($title as $title) {
    echo $post->title;
}
```

### Chunking Results
Elasticsearch indices can grow quite huge. Your application may run out of memory if you would attempt to load tens of thousands of Elasticsearch documents via
the `all` or `get` methods without an upper bound. Therefore, the default amount of documents fetched is set to `10`. To change this, use the `take` method:

```php
use App\Models\Post;

$posts = Post::take(500)->get();
```

### Retrieving individual Models
In addition to retrieving all the documents matching a given query, you may also retrieve single documents using the `find`, `first`, or `firstWhere` methods.
Instead of returning a collection of models, these methods return a single model instance:

```php
use App\Models\Post;

// Retrieve a model by its ID...
$posts = Post::find('AVp_tCaAoV7YQD3Esfmp');

// Retrieve the first model matching the query constraints...
$post = Post::where('published', 1)->first();

// Alternative to retrieving the first model matching the query constraints...
$post = Post::firstWhere('published', 1);```
```

Sometimes you may wish to retrieve the first result of a query or perform some other action if no results are found. The `firstOr` method will return the first
result matching the query or, if no results are found, execute the given closure. The value returned by the closure will be considered the result of the
`firstOr` method:

```php
use App\Models\Post;

$model = Post::where('tags', '>', 3)->firstOr(function () {
    // ...
});
```

### Not Found Exceptions
Sometimes you may wish to throw an exception if a model is not found. This is particularly useful in routes or controllers. The `findOrFail` and `firstOrFail`
methods will retrieve the first result of the query; however, if no result is found, a
[`Matchory\Elasticsearch\Exceptions\DocumentNotFoundException`](./src/Exceptions/DocumentNotFoundException.php) will be thrown:

```php
$post = Post::findOrFail('AVp_tCaAoV7YQD3Esfmp');

$post = Post::where('published', true)->firstOrFail();
```

If the `DocumentNotFoundException` is not caught, a 404 HTTP response is automatically sent back to the client:

```php
use App\Models\Post;

Route::get('/api/posts/{id}', function ($id) {
    return Post::findOrFail($id);
});
```

### Inserting and Updating Models
#### Inserts
To insert a new document into the index, you should instantiate a new model instance and set attributes on the model. Then, call the `save` method on the model
instance:

```php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    /**
     * Create a new post instance.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Validate the request...

        $post = new Post;
        $post->title = $request->title;

        $post->save();
    }
}
```

In this example, we assign the `name` field from the incoming HTTP request to the `name` attribute of the `App\Models\Post` model instance. When we call the
`save` method, a document will be inserted into the index.

Alternatively, you may use the `create` method to "save" a new model using a single PHP statement. The inserted model instance will be returned to you by the
`create` method:

```php
use App\Models\Post;

$post = Post::create([
'title' => 'Searching efficiently',
]);
```

However, before using the create method, you will need to specify either a `fillable` or `guarded` property on your model class. These properties are required
because all Elasticsearch models are protected against mass assignment vulnerabilities by default. To learn more about mass assignment, please consult the
[mass assignment documentation](https://laravel.com/docs/8.x/eloquent#mass-assignment).

#### Updates
The `save` method may also be used to update models that already exist in the index. To update a model, you should retrieve it and set any attributes you wish
to update. Then, you should call the model's `save` method.

The `save()` method may also be used to update models that already exist. To update a model, you should retrieve it, set any attributes you wish to update, and
then call the save method.

```php
use App\Models\Post;

$post = Post::find('AVp_tCaAoV7YQD3Esfmp');

$post->title = 'Modified Post Title';

$post->save();
```

#### Examining Attribute Changes
Elasticsearch provides the `isDirty`, `isClean`, and `wasChanged` methods to examine the internal state of your model and determine how its attributes have
changed from when the model was originally retrieved.

The `isDirty` method determines if any of the model's attributes have been changed since the model was retrieved. You may pass a specific attribute name to the
`isDirty` method to determine if a particular attribute is _dirty_. The `isClean` will determine if an attribute has remained unchanged since the model was
retrieved. This method also accepts an optional attribute argument:

```php
use App\Models\Author;

$author = Author::create([
'first_name' => 'Moritz',
'last_name' => 'Friedrich',
'title' => 'Developer',
]);

$author->title = 'Painter';

$author->isDirty(); // true
$author->isDirty('title'); // true
$author->isDirty('first_name'); // false

$author->isClean(); // false
$author->isClean('title'); // false
$author->isClean('first_name'); // true

$author->save();

$author->isDirty(); // false
$author->isClean(); // true
```

The `wasChanged` method determines if any attributes were changed when the model was last saved within the current request cycle. If needed, you may pass an
attribute name to see if a particular attribute was changed:

```php
use App\Models\Author;

$author = Author::create([
'first_name' => 'Taylor',
'last_name' => 'Otwell',
'title' => 'Developer',
]);

$author->title = 'Painter';

$author->save();

$author->wasChanged(); // true
$author->wasChanged('title'); // true
$author->wasChanged('first_name'); // false
```

The `getOriginal` method returns an array containing the original attributes of the model regardless of any changes to the model since it was retrieved. If
needed, you may pass a specific attribute name to get the original value of a particular attribute:

```php
use App\Models\Author;

$author = Author::find(1);

$author->name; // John
$author->email; // john@example.com

$author->name = "Jack";
$author->name; // Jack

$author->getOriginal('name'); // John
$author->getOriginal(); // Array of original attributes...
```

#### Mass Assignment
You may use the `create` method to "save" a new model using a single PHP statement. The inserted model instance will be returned to you by the method:

```php
use App\Models\Post;

$post = Post::create([
    'title' => 'Searching effectively',
]);
```

However, before using the `create` method, you will need to specify either a `fillable` or `guarded` property on your model class. These properties are required
because all Elasticsearch models are protected against mass assignment vulnerabilities by default.

A mass assignment vulnerability occurs when a user passes an unexpected HTTP request field and that field changes a field in your index that you did not expect.

So, to get started, you should define which model attributes you want to make mass assignable. You may do this using the `fillable` property on the model. For
example, let's make the `title` attribute of our `Post` model mass assignable:

```php
namespace App\Models;

use Matchory\Elasticsearch\Model;

class Post extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title'];
}
```

Once you have specified which attributes are mass assignable, you may use the `create` method to insert a new document in the index. The `create` method returns
the newly created model instance:

```php
$post = Post::create(['title' => 'Searching effectively']);
```

If you already have a model instance, you may use the `fill` method to populate it with an array of attributes:

```php
$post->fill(['title' => 'Searching more effectively']);
```

#### Allowing Mass Assignment
If you would like to make all of your attributes mass assignable, you may define your model's `guarded` property as an empty array. If you choose to un-guard
your model, you should take special care to always hand-craft the arrays passed to Elasticsearch's `fill`, `create`, and `update` methods:

```php
/**
 * The attributes that aren't mass assignable.
 *
 * @var array
 */
protected $guarded = [];
```

#### Upserts
There is currently no convenience wrapper for upserting documents (inserting or updating depending on whether models exist). If you're interested in such a
capability, please open an issue.

##### Deleting Models
To delete a model, call the `delete` method on a model instance:

```php
use App\Models\Post;

$post = Post::find('AVp_tCaAoV7YQD3Esfmp');

$post->delete();
```

#### Deleting An Existing Model By Its ID
In the example above, we are retrieving the model from the index before calling the `delete` method. However, if you know the ID of the model, you may delete
the model without explicitly retrieving it by calling the `destroy` method. In addition to accepting the single ID, the `destroy` method will accept multiple
IDs, an array of IDs, or a collection of IDs:

```php
use App\Models\Post;

Post::destroy(1);

Post::destroy(1, 2, 3);

Post::destroy([1, 2, 3]);

Post::destroy(collect([1, 2, 3]));
```

> **Important:**  
> The `destroy` method loads each model individually and calls the `delete` method so that the `deleting` and `deleted` events are properly dispatched for
> each model.

### Query Scopes
Query scopes are implemented exactly the way as they are in Eloquent.

#### Global Scopes
Global scopes allow you to add constraints to all queries for a given model. Writing your own global scopes can provide a convenient, easy way to make sure
every query for a given model receives certain constraints.

##### Writing Global Scopes
Writing a global scope is simple. First, define a class that implements the
[`Matchory\Elasticsearch\Interfaces\ScopeInterface`](./src/Interfaces/ScopeInterface.php) interface. Laravel does not have a conventional location that you
should place scope classes, so you are free to place this class in any directory that you wish.

The `ScopeInterface` requires you to implement one method: `apply`. The `apply` method may add constraints or other types of clauses to the query as needed:

```php
namespace App\Scopes;

use Matchory\Elasticsearch\Query;
use Matchory\Elasticsearch\Model;
use Matchory\Elasticsearch\Interfaces\ScopeInterface;

class AncientScope implements ScopeInterface
{
    /**
     * Apply the scope to a given Elasticsearch query builder.
     *
     * @param  \Matchory\Elasticsearch\Query  $query
     * @param  \Matchory\Elasticsearch\Model  $model
     * @return void
     */
    public function apply(Query $query, Model $model)
    {
        $query->where('created_at', '<', now()->subYears(2000));
    }
}
```

##### Applying Global Scopes
To assign a global scope to a model, you should override the model's booted method and invoke the model's `addGlobalScope` method. The `addGlobalScope` method
accepts an instance of your scope as its only argument:

```php
namespace App\Models;

use App\Scopes\AncientScope;
use Matchory\Elasticsearch\Model;

class Post extends Model
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new AncientScope);
    }
}
```

##### Anonymous Global Scopes
Elasticsearch also allows you to define global scopes using closures, which is particularly useful for simple scopes that do not warrant a separate class of
their own. When defining a global scope using a closure, you should provide a scope name of your own choosing as the first argument to the
`addGlobalScope` method:

```php
namespace App\Models;

use Matchory\Elasticsearch\Query;
use Matchory\Elasticsearch\Model;

class Post extends Model
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ancient', function (Query $query) {
            $query->where('created_at', '<', now()->subYears(2000));
        });
    }
}
```

##### Removing Global Scopes
If you would like to remove a global scope for a given query, you may use the `withoutGlobalScope` method. This method accepts the class name of the global
scope as its only argument:

```php
Post::withoutGlobalScope(AncientScope::class)->get();
```

Or, if you defined the global scope using a closure, you should pass the string name that you assigned to the global scope:

```php
Post::withoutGlobalScope('ancient')->get();
```

If you would like to remove several or even all of the query's global scopes, you may use the `withoutGlobalScopes` method:

```php
// Remove all of the global scopes...
Post::withoutGlobalScopes()->get();
```

```php
// Remove some of the global scopes...
Post::withoutGlobalScopes([
    FirstScope::class,
    SecondScope::class
])->get();
```

#### Local Scopes
Local scopes allow you to define common sets of query constraints that you may easily re-use throughout your application. For example, you may need to
frequently retrieve all posts that are considered "popular".

##### Writing local scopes
To define a scope, prefix an Elasticsearch model method with scope. Scopes should always return a query builder instance:

```php
namespace App\Models;

use Matchory\Elasticsearch\Model;

class Post extends Model
{
    /**
     * Scope a query to only include popular posts.
     *
     * @param  \Matchory\Elasticsearch\Query  $query
     * @return \Matchory\Elasticsearch\Query
     */
    public function scopePopular(Query $query): Query
    {
        return $query->where('votes', '>', 100);
    }

    /**
     * Scope a query to only include published posts.
     *
     * @param  \Matchory\Elasticsearch\Query  $query
     * @return \Matchory\Elasticsearch\Query
     */
    public function scopePublished(Query $query): Query
    {
        return $query->where('published', 1);
    }
}
```

##### Utilizing local scopes
Once the scope has been defined, you may call the scope methods when querying the model. However, you should not include the scope prefix when calling the
method. You can even chain calls to various scopes:

```php
use App\Models\Post;

$posts = Post::popular()->published()->orderBy('created_at')->get();
```

#### Dynamic Scopes
Sometimes you may wish to define a scope that accepts parameters. To get started, just add your additional parameters to your scope method's signature. Scope
parameters should be defined after the `$query` parameter:

```php
namespace App\Models;

use Matchory\Elasticsearch\Model;

class Post extends Model
{
    /**
     * Scope a query to only include posts of a given type.
     *
     * @param  \Matchory\Elasticsearch\Query  $query
     * @param  mixed  $type
     * @return \Matchory\Elasticsearch\Query
     */
    public function scopeOfType(Query $query, $type): Query
    {
        return $query->where('type', $type);
    }
}
```

Once the expected arguments have been added to your scope method's signature, you may pass the arguments when calling the scope:

```
$posts = Post::ofType('news')->get();
```

### Comparing Models
Sometimes you may need to determine if two models are the "same". The is method may be used to quickly verify two models have the same ID, index, type, and
connection:

```php
if ($post->is($anotherPost)) {
    //
}
```

### Events
Elasticsearch models dispatch several events, allowing you to hook into the following moments in a model's lifecycle: `retrieved`, `creating`, `created`,
`updating`, `updated`, `saving`, `saved`, `deleting`, `deleted`, `restoring`, `restored`, and `replicating`.

The `retrieved` event will dispatch when an existing model is retrieved from the index. When a new model is saved for the first time, the `creating` and
`created` events will dispatch. The `updating` / `updated` events will dispatch when an existing model is modified, and the `save` method is called. The
`saving` / `saved` events will dispatch when a model is created or updated - even if the model's attributes have not been changed.

To start listening to model events, define a `dispatchesEvents` property on your Elasticsearch model. This property maps various points of the Elasticsearch
model's lifecycle to your own [event classes](https://laravel.com/docs/8.x/events). Each model event class should expect to receive an instance of the affected
model via its constructor:

```php
namespace App\Models;

use Matchory\Elasticsearch\Model;
use App\Events\UserDeleted;
use App\Events\UserSaved;

class Post extends Model
{
    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'saved' => PostSaved::class,
        'deleted' => PostDeleted::class,
    ];
}
```

After defining and mapping your events, you may use [event listeners](https://laravel.com/docs/8.x/events#defining-listeners) to handle the events.

#### Using Closures
Instead of using custom event classes, you may register closures that execute when various model events are dispatched. Typically, you should register these
closures in the `booted` method of your model:

```php
namespace App\Models;

use Matchory\Elasticsearch\Model;

class Post extends Model
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::created(function ($post) {
            //
        });
    }
}
```

If needed, you may utilize [queueable anonymous event listeners](https://laravel.com/docs/8.x/events#queuable-anonymous-event-listeners) when registering model
events. This will instruct Laravel to execute the model event listener in the background using your application's [queue](https://laravel.com/docs/8.x/queues):

```php
use function Illuminate\Events\queueable;

static::created(queueable(function ($post): void {
    //
}));
```

##### Accessors & Mutators

###### Defining An Accessor
To define an `accessor`, create a `getFooAttribute` method on your model where `Foo` is the "studly" cased name of the field you wish to access. In this
example, we'll define an accessor for the `title` attribute. The accessor will automatically be called by model when attempting to retrieve the value of the
`title` attribute:
```php

namespace App;

use Matchory\Elasticsearch\Model;

class post extends Model
{
    /**
     * Get the post title.
     *
     * @param  string  $value
     * @return string
     */
    public function getTitleAttribute(string $value): string
    {
        return ucfirst($value);
    }
}
```

As you can see, the original value of the field is passed to the accessor, allowing you to manipulate and return the value. To access the value of the accessor,
you may simply access the `title` attribute on a model instance:
```php
$post = App\Post::find(1);

$title = $post->title;
```

Occasionally, you may need to add array attributes that do not have a corresponding field in your index. To do so, simply define an accessor for the value:
```php
public function getIsPublishedAttribute(): bool
{
    return $this->attributes['status'] === 1;
}
```

Once you have created the accessor, just add the value to the `appends` property on the model:

```php
protected $appends = ['is_published'];
```

Once the attribute has been added to the appends list, it will be included in model's array.

###### Defining A Mutator
To define a mutator, define a `setFooAttribute` method on your model where `Foo` is the "studly" cased name of the field you wish to access. So, again, let's
define a mutator for the `title` attribute. This mutator will be automatically called when we attempt to set the value of the `title`attribute on the model:
```php
namespace App;

use Matchory\Elasticsearch\Model;

class post extends Model
{
    /**
     * Set the post title.
     *
     * @param  string  $value
     * @return string
     */
    public function setTitleAttribute(string $value): string
    {
        return strtolower($value);
    }
}
```

The mutator will receive the value that is being set on the attribute, allowing you to manipulate the value and set the manipulated value on the model's
internal `$attributes` property. So, for example, if we attempt to set the title attribute to `Awesome post to read`:
```php
$post = App\Post::find(1);

$post->title = 'Awesome post to read';
```

In this example, the setTitleAttribute function will be called with the value `Awesome post to read`. The mutator will then apply the strtolower function to the
name and set its resulting value in the internal $attributes array.

#### Muting Events
You may occasionally need to temporarily "mute" all events fired by a model. You may achieve this using the `withoutEvents` method. The `withoutEvents` method
accepts a closure as its only argument. Any code executed within this closure will not dispatch model events. For example, the following example will fetch and
delete an `App\Models\Post` instance without dispatching any model events. Any value returned by the closure will be returned by the `withoutEvents` method:

```php
use App\Models\Post;

$post = Post::withoutEvents(function () use () {
Post::findOrFail(1)->delete();

    return Post::find(2);
});
```

#### Saving A Single Model Without Events
Sometimes you may wish to "save" a given model without dispatching any events. You may accomplish this using the `saveQuietly` method:

```php
$post = Post::findOrFail(1);

$post->title = 'Other search strategies';

$post->saveQuietly();
```

### Replicating Models
You may create an unsaved copy of an existing model instance using the replicate method. This method is particularly useful when you have model instances that
share many of the same attributes:

```php
use App\Models\Address;

$shipping = Address::create([
    'type' => 'shipping',
    'line_1' => '123 Example Street',
    'city' => 'Victorville',
    'state' => 'CA',
    'postcode' => '90001',
]);

$billing = $shipping->replicate()->fill([
    'type' => 'billing'
]);

$billing->save();
```

### Mutators and Casting
Accessors, mutators, and attribute casting allow you to transform Elasticsearch attribute values when you retrieve or set them on model instances. For example,
you may want to use the [Laravel encrypter](https://laravel.com/docs/8.x/encryption) to encrypt a value while it is stored in the index, and then automatically
decrypt the attribute when you access it on an Elasticsearch model. Or, you may want to convert a JSON string that is stored in your index to an array when it
is accessed via your Elasticsearch model.

#### Accessors & Mutators
##### Defining An Accessor
An accessor transforms an Elasticsearch attribute value when it is accessed. To define an accessor, create a `get{Attribute}Attribute` method on your model
where `{Attribute}` is the "studly" cased name of the field you wish to access.

In this example, we'll define an accessor for the `first_name` attribute. The accessor will automatically be called by Elasticsearch when attempting to retrieve
the value of the `first_name` attribute:

```php
namespace App\Models;

use Matchory\Elasticsearch\Model;

class User extends Model
{
    /**
     * Get the user's first name.
     *
     * @param  string  $value
     * @return string
     */
    public function getFirstNameAttribute(string $value): string
    {
        return ucfirst($value);
    }
}
```

As you can see, the original value of the field is passed to the accessor, allowing you to manipulate and return the value. To access the value of the accessor,
you may simply access the `first_name` attribute on a model instance:

```php
use App\Models\User;

$user = User::find(1);

$firstName = $user->first_name;
```

You are not limited to interacting with a single attribute within your accessor. You may also use accessors to return new, computed values from existing
attributes:

```php
/**
 * Get the user's full name.
 *
 * @return string
 */
public function getFullNameAttribute(): string
{
    return "{$this->first_name} {$this->last_name}";
}
```

##### Defining A Mutator
A mutator transforms an Elasticsearch attribute value when it is set. To define a mutator, define a `set{Attribute}Attribute` method on your model where
`{Attribute}` is the "studly" cased name of the field you wish to access.

Let's define a mutator for the `first_name` attribute. This mutator will be automatically called when we attempt to set the value of the `first_name` attribute
on the model:

```php
namespace App\Models;

use Matchory\Elasticsearch\Model;

class User extends Model
{
    /**
     * Set the user's first name.
     *
     * @param  string  $value
     * @return void
     */
    public function setFirstNameAttribute(string $value): void
    {
        $this->attributes['first_name'] = strtolower($value);
    }
}
```

The mutator will receive the value that is being set on the attribute, allowing you to manipulate the value and set the manipulated value on the Elasticsearch
model's internal `$attributes` property. To use our mutator, we only need to set the `first_name` attribute on an Elasticsearch model:

```php
use App\Models\User;

$user = User::find(1);

$user->first_name = 'Sally';
```

In this example, the `setFirstNameAttribute` function will be called with the value `Sally`. The mutator will then apply the `strtolower` function to the name
and set its resulting value in the internal `$attributes` array.

#### Attribute Casting
Attribute casting provides functionality similar to accessors and mutators without requiring you to define any additional methods on your model. Instead, your
model's `$casts` property provides a convenient method of converting attributes to common data types.

The `$casts` property should be an array where the key is the name of the attribute being cast, and the value is the type you wish to cast the field to. The
supported cast types are:

- `array`
- `boolean`
- `collection`
- `date`
- `datetime`
- `decimal:<digits>`
- `double`
- `encrypted`
- `encrypted:array`
- `encrypted:collection`
- `encrypted:object`
- `float`
- `integer`
- `object`
- `real`
- `string`
- `timestamp`

To demonstrate attribute casting, let's cast the `is_admin` attribute, which is stored in our index as an integer (`0` or `1`) to a boolean value:

```php
namespace App\Models;

use Matchory\Elasticsearch\Model;

class User extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_admin' => 'boolean',
    ];
}
```

After defining the cast, the `is_admin` attribute will always be cast to a boolean when you access it, even if the underlying value is stored in the index as an
integer:

```php
$user = App\Models\User::find(1);

if ($user->is_admin) {
    //
}
```

> **Note:** Attributes that are `null` will not be cast.

##### Date Casting
You may cast date attributes by defining them within your model's `$cast` property array. Typically, dates should be cast using the `datetime` cast.

When defining a `date` or `datetime` cast, you may also specify the date's format. This format will be used when the
[model is serialized to an array or JSON](https://laravel.com/docs/8.x/eloquent-serialization):

```php
/**
 * The attributes that should be cast.
 *
 * @var array
 */
protected $casts = [
    'created_at' => 'datetime:Y-m-d',
];
```

When a field is cast as a date, you may set its value to a UNIX timestamp, date string (`Y-m-d`), date-time string, or a `DateTime` / `Carbon` instance. The
date's value will be correctly converted and stored in your index:

You may customize the default serialization format for all of your model's dates by defining a `serializeDate` method on your model. This method does not affect
how your dates are formatted for storage in the index:

```php
/**
 * Prepare a date for array / JSON serialization.
 *
 * @param  \DateTimeInterface  $date
 * @return string
 */
protected function serializeDate(DateTimeInterface $date)
{
    return $date->format('Y-m-d');
}
```

To specify the format that should be used when actually storing a model's dates within your index, you should define a `$dateFormat` property on your model:

```php
/**
 * The storage format of the model's date fields.
 *
 * @var string
 */
protected $dateFormat = 'U';
```

#### Custom Casts
Laravel has a variety of built-in, helpful cast types; however, you may occasionally need to define your own cast types. You may accomplish this by defining a
class that implements the `CastsAttributes` interface.

Classes that implement this interface must define a `get` and `set` method. The `get` method is responsible for transforming a raw value from the index into a
cast value, while the `set` method should transform a cast value into a raw value that can be stored in the index. As an example, we will re-implement the
built-in `json` cast type as a custom cast type:

> **Note:** Due to type incompatibility, you will need to use different casts for Eloquent and Elasticsearch models, or omit the parameter type.

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Json implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Matchory\Elasticsearch\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    public function get($model, $key, $value, $attributes)
    {
        return json_decode($value, true);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Matchory\Elasticsearch\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        return json_encode($value);
    }
}
```

Once you have defined a custom cast type, you may attach it to a model attribute using its class name:

```php
    namespace App\Models;

    use App\Casts\Json;
    use Matchory\Elasticsearch\Model;

    class User extends Model
    {
        /**
         * The attributes that should be cast.
         *
         * @var array
         */
        protected $casts = [
            'options' => Json::class,
        ];
    }
```

##### Value Object Casting
You are not limited to casting values to primitive types. You may also cast values to objects. Defining custom casts that cast values to objects is very similar
to casting to primitive types; however, the `set` method should return an array of key / value pairs that will be used to set raw, storable values on the model.

As an example, we will define a custom cast class that casts multiple model values into a single `Address` value object. We will assume the `Address` value has
two public properties: `lineOne` and `lineTwo`:

```php
namespace App\Casts;

use App\Models\Address as AddressModel;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class Address implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Matchory\Elasticsearch\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return \App\Models\Address
     */
    public function get($model, $key, $value, $attributes)
    {
        return new AddressModel(
            $attributes['address_line_one'],
            $attributes['address_line_two']
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Matchory\Elasticsearch\Model  $model
     * @param  string  $key
     * @param  \App\Models\Address  $value
     * @param  array  $attributes
     * @return array
     */
    public function set($model, $key, $value, $attributes)
    {
        if (! $value instanceof AddressModel) {
            throw new InvalidArgumentException('The given value is not an Address instance.');
        }

        return [
            'address_line_one' => $value->lineOne,
            'address_line_two' => $value->lineTwo,
        ];
    }
}
```

When casting to value objects, any changes made to the value object will automatically be synced back to the model before the model is saved:
```php
use App\Models\User;

$user = User::find(1);

$user->address->lineOne = 'Updated Address Value';

$user->save();
```

> **Tip:** If you plan to serialize your Elasticsearch models containing value objects to JSON or arrays, you should implement the
> `Illuminate\Contracts\Support\Arrayable` and `JsonSerializable` interfaces on the value object.

##### Array / JSON Serialization
When an Elasticsearch model is converted to an array or JSON using the `toArray` and `toJson` methods, your custom cast value objects will typically be
serialized as well as long as they implement the `Illuminate\Contracts\Support\Arrayable` and `JsonSerializable` interfaces. However, when using value objects
provided by third-party libraries, you may not have the ability to add these interfaces to the object.

Therefore, you may specify that your custom cast class will be responsible for serializing the value object. To do so, your custom class cast should implement
the `Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes` interface. This interface states that your class should contain a `serialize` method
which should return the serialized form of your value object:

```php
/**
 * Get the serialized representation of the value.
 *
 * @param  \Illuminate\Database\Eloquent\Model|\Matchory\Elasticsearch\Model  $model
 * @param  string  $key
 * @param  mixed  $value
 * @param  array  $attributes
 * @return mixed
 */
public function serialize($model, string $key, $value, array $attributes)
{
    return (string) $value;
}
```

##### Inbound Casting

Occasionally, you may need to write a custom cast that only transforms values that are being set on the model and does not perform any operations when
attributes are being retrieved from the model. A classic example of an inbound only cast is a "hashing" cast. Inbound only custom casts should implement the
`CastsInboundAttributes` interface, which only requires a `set` method to be defined.

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;

class Hash implements CastsInboundAttributes
{
    /**
     * The hashing algorithm.
     *
     * @var string
     */
    protected $algorithm;

    /**
     * Create a new cast class instance.
     *
     * @param  string|null  $algorithm
     * @return void
     */
    public function __construct($algorithm = null)
    {
        $this->algorithm = $algorithm;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Matchory\Elasticsearch\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        return is_null($this->algorithm)
                    ? bcrypt($value)
                    : hash($this->algorithm, $value);
    }
}
```

##### Cast Parameters
When attaching a custom cast to a model, cast parameters may be specified by separating them from the class name using a `:` character and comma-delimiting
multiple parameters. The parameters will be passed to the constructor of the cast class:

```php
/**
 * The attributes that should be cast.
 *
 * @var array
 */
protected $casts = [
    'secret' => Hash::class.':sha256',
];
```

##### Castables
You may want to allow your application's value objects to define their own custom cast classes. Instead of attaching the custom cast class to your model, you
may alternatively attach a value object class that implements the `Illuminate\Contracts\Database\Eloquent\Castable` interface:

```php
use App\Models\Address;

protected $casts = [
    'address' => Address::class,
];
```

Objects that implement the `Castable` interface must define a `castUsing` method that returns the class name of the custom caster class that is responsible for
casting to and from the `Castable` class:

```php
namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Castable;
use App\Casts\Address as AddressCast;

class Address implements Castable
{
    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return string
     */
    public static function castUsing(array $arguments): string
    {
        return AddressCast::class;
    }
}
```

When using `Castable` classes, you may still provide arguments in the `$casts` definition. The arguments will be passed to the `castUsing` method:

```php
use App\Models\Address;

protected $casts = [
    'address' => Address::class.':argument',
];
```

##### Castables & Anonymous Cast Classes
By combining "castables" with PHP's [anonymous classes](https://www.php.net/manual/en/language.oop5.anonymous.php), you may define a value object and its
casting logic as a single castable object. To accomplish this, return an anonymous class from your value object's `castUsing` method. The anonymous class should
implement the `CastsAttributes` interface:

```php
namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Address implements Castable
{
    // ...

    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                return new Address(
                    $attributes['address_line_one'],
                    $attributes['address_line_two']
                );
            }

            public function set($model, $key, $value, $attributes)
            {
                return [
                    'address_line_one' => $value->lineOne,
                    'address_line_two' => $value->lineTwo,
                ];
            }
        };
    }
}
```

### Route Model Binding
When injecting a model ID to a route or controller action, you will often query the Elasticsearch index to retrieve the model that corresponds to that ID.
Laravel route model binding provides a convenient way to automatically inject the model instances directly into your routes. For example, instead of injecting a
user's ID, you can inject the entire User model instance that matches the given ID.

#### Implicit Binding
Laravel automatically resolves Elasticsearch models defined in routes or controller actions whose type-hinted variable names match a route segment name. For
example:

```php
use App\Models\Post;

Route::get('/posts/{post}', function (Post $post) {
    return $post->content;
});
```

Since the `$post` variable is type-hinted as the `App\Models\Post` Elasticsearch model, and the variable name matches the `{post}` URI segment, Laravel will 
automatically inject the model instance that has an ID matching the corresponding value from the request URI. If a matching model instance is not found in the 
database, a `404` HTTP response will automatically be generated.

Of course, implicit binding is also possible when using controller methods. Again, note the `{post}` URI segment matches the `$post` variable in the controller
which contains an `App\Models\Post` type-hint:

```php
use App\Http\Controllers\PostController;
use App\Models\Post;

// Route definition...
Route::get('/posts/{post}', [PostController::class, 'show']);

// Controller method definition...
public function show(Post $post): View
{
    return view('post.full', ['post' => $post]);
}
```

#### Customizing The Key
Sometimes you may wish to resolve Elasticsearch models using a field other than `_id`. To do so, you may specify the field in the route parameter definition:

```php
use App\Models\Post;

Route::get('/posts/{post:slug}', fn(Post $post): Post => $post);
```

If you would like model binding to always use an index field other than `_id` when retrieving a given model class, you may override the `getRouteKeyName` method
on the Elasticsearch model:

```php
/**
 * Get the route key for the model.
 *
 * @return string
 */
public function getRouteKeyName(): string
{
    return 'slug';
}
```

#### Customizing Missing Model Behavior
Typically, a `404` HTTP response will be generated if an implicitly bound model is not found. However, you may customize this behavior by calling the missing
method when defining your route. The missing method accepts a closure that will be invoked if an implicitly bound model can not be found:

```php
use App\Http\Controllers\LocationsController;
use Illuminate\Http\Request;

Route::get('/locations/{location:slug}', [LocationsController::class, 'show'])
    ->missing(fn(Request $request) => Redirect::route('locations.index')
    ->name('locations.view');
```

#### Explicit Binding
You are not required to use Laravel's implicit, convention based model resolution in order to use model binding. You can also explicitly define how route
parameters correspond to models. To register an explicit binding, use the router's model method to specify the class for a given parameter. You should define
your explicit model bindings at the beginning of the `boot` method of your `RouteServiceProvider` class:

```php
use App\Models\Post;
use Illuminate\Support\Facades\Route;

/**
 * Define your route model bindings, pattern filters, etc.
 *
 * @return void
 */
public function boot():void
{
    Route::model('post', Post::class);

    // ...
}
```  

Next, define a route that contains a `{post}` parameter:

```php
use App\Models\Post;

Route::get('/posts/{post}', function (Post $post) {
    // ...
});
```

Since we have bound all `{post}` parameters to the `App\Models\Post` model, an instance of that class will be injected into the route. So, for example, a
request to `posts/1` will inject the `Post` instance from the index which has an ID of `1`.

If a matching model instance is not found in the index, a `404` HTTP response will be automatically generated.

#### Customizing The Resolution Logic
If you wish to define your own model binding resolution logic, you may use the `Route::bind` method. The closure you pass to the bind method will receive the
value of the URI segment and should return the instance of the class that should be injected into the route. Again, this customization should take place in the
`boot` method of your application's `RouteServiceProvider`:

```php
use App\Models\Post;
use Illuminate\Support\Facades\Route;

/**
 * Define your route model bindings, pattern filters, etc.
 *
 * @return void
 */
public function boot(): void
{
    Route::bind('post', function (string $value): Post {
        return Post::where('title', $value)->firstOrFail();
    });

    // ...
}
```

Alternatively, you may override the `resolveRouteBinding` method on your Elasticsearch model. This method will receive the value of the URI segment and should
return the instance of the class that should be injected into the route:

```php
/**
 * Retrieve the model for a bound value.
 *
 * @param  mixed  $value
 * @param  string|null  $field
 * @return \Matchory\Elasticsearch\Model|null
 */
public function resolveRouteBinding($value, ?string $field = null): ?self
{
    return $this->where('name', $value)->firstOrFail();
}
```

If a route is utilizing implicit binding scoping, the `resolveChildRouteBinding` method will be used to resolve the child binding of the parent model:

```php
/**
 * Retrieve the child model for a bound value.
 *
 * @param  string  $childType
 * @param  mixed  $value
 * @param  string|null  $field
 * @return \Matchory\Elasticsearch\Model|null
 */
public function resolveChildRouteBinding(string $childType, $value, ?string $field): ?self
{
    return parent::resolveChildRouteBinding($childType, $value, $field);
}
```

Usage as a query builder
------------------------
You can use the `ES` facade to access the query builder directly, from anywhere in your application.

### Creating a new index
```php
ES::create('my_index');
    
# or 
    
ES::index('my_index')->create();
```

### Creating index with custom options (optional)
```php
use Matchory\Elasticsearch\Facades\ES;
use Matchory\Elasticsearch\Index;

ES::index('my_index')->create(function(Index $index) {
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
    
ES::create('my_index', function(Index $index){
  
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

### Dropping an index
```php
ES::drop("my_index");
    
# or

ES::index("my_index")->drop();
```

### Running queries
To run a query, start by (optionally) selecting the connection and index.
```php
$documents = ES::connection("default")
                ->index("my_index")
                ->type("my_type")
                ->get();    # return a collection of results
```

You can shorten the above query to:
```php
$documents = ES::type("my_type")->get();    # return a collection of results
```

Explicitly setting connection or index name in the query overrides configuration in `config/es.php`.

### Getting documents by id
```php
ES::type("my_type")->id(3)->first();
    
# or
    
ES::type("my_type")->_id(3)->first();
```

### Sorting
```php
ES::type("my_type")->orderBy("created_at", "desc")->get();
    
# Sorting with text search score
    
ES::type("my_type")->orderBy("_score")->get();
```

### Limit and offset
```php
ES::type("my_type")->take(10)->skip(5)->get();
```

### Select only specific fields
```php
ES::type("my_type")->select("title", "content")->take(10)->skip(5)->get();
```

### Where clause
```php
ES::type("my_type")->where("status", "published")->get();

# or

ES::type("my_type")->where("status", "=", "published")->get();
```

### Where greater than
```php
ES::type("my_type")->where("views", ">", 150)->get();
```

### Where greater than or equal
```php
ES::type("my_type")->where("views", ">=", 150)->get();
```

### Where less than
```php
ES::type("my_type")->where("views", "<", 150)->get();
```

### Where less than or equal
```php
ES::type("my_type")->where("views", "<=", 150)->get();
```

### Where like
```php
ES::type("my_type")->where("title", "like", "foo")->get();
```

### Where field exists
```php
ES::type("my_type")->where("hobbies", "exists", true)->get(); 

# or 

ES::type("my_type")->whereExists("hobbies", true)->get();
```    

### Where in clause
```php
ES::type("my_type")->whereIn("id", [100, 150])->get();
```

### Where between clause
```php
ES::type("my_type")->whereBetween("id", 100, 150)->get();

# or 

ES::type("my_type")->whereBetween("id", [100, 150])->get();
```

### Where not clause
```php
ES::type("my_type")->whereNot("status", "published")->get(); 

# or

ES::type("my_type")->whereNot("status", "=", "published")->get();
```

### Where not greater than
```php
ES::type("my_type")->whereNot("views", ">", 150)->get();
```

### Where not greater than or equal
```php
ES::type("my_type")->whereNot("views", ">=", 150)->get();
```

### Where not less than
```php
ES::type("my_type")->whereNot("views", "<", 150)->get();
```

### Where not less than or equal
```php
ES::type("my_type")->whereNot("views", "<=", 150)->get();
```

### Where not like
```php
ES::type("my_type")->whereNot("title", "like", "foo")->get();
```

### Where not field exists
```php
ES::type("my_type")->whereNot("hobbies", "exists", true)->get(); 

# or

ES::type("my_type")->whereExists("hobbies", true)->get();
```

### Where not in clause
```php
ES::type("my_type")->whereNotIn("id", [100, 150])->get();
```

### Where not between clause
```php
ES::type("my_type")->whereNotBetween("id", 100, 150)->get();

# or

ES::type("my_type")->whereNotBetween("id", [100, 150])->get();
```

### Search by a distance from a geo point
```php
ES::type("my_type")->distance("location", ["lat" => -33.8688197, "lon" => 151.20929550000005], "10km")->get();

# or

ES::type("my_type")->distance("location", "-33.8688197,151.20929550000005", "10km")->get();

# or

ES::type("my_type")->distance("location", [151.20929550000005, -33.8688197], "10km")->get();  
```

### Search using array queries
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

### Search the entire document
```php
ES::type("my_type")->search("hello")->get();
    
# search with Boost = 2
    
ES::type("my_type")->search("hello", 2)->get();

# search within specific fields with different weights

ES::type("my_type")->search("hello", function($search){
	$search->boost(2)->fields(["title" => 2, "content" => 1])
})->get();
```

### Search with highlight fields
```php
$doc = ES::type("my_type")->highlight("title")->search("hello")->first();

# Multiple fields Highlighting is allowed.

$doc = ES::type("my_type")->highlight("title", "content")->search("hello")->first();

# Return all highlights as array using $doc->getHighlights() method.

$doc->getHighlights();

# Also you can return only highlights of specific field.

$doc->getHighlights("title");
```

### Return only first document
```php
ES::type("my_type")->search("hello")->first();
```

### Return only count
```php
ES::type("my_type")->search("hello")->count();
```

### Scan-and-Scroll queries
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

### Paginate results with 5 documents per page
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

### Getting the query array without execution
```php
ES::type("my_type")->search("hello")->where("views", ">", 150)->query();
```

### Getting the original elasticsearch response
```php
ES::type("my_type")->search("hello")->where("views", ">", 150)->response();
```

### Ignoring bad HTTP response
```php
ES::type("my_type")->ignore(404, 500)->id(5)->first();
```

### Query Caching (Laravel & Lumen)
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

### Executing elasticsearch raw queries
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

### Insert a new document
```php
ES::type("my_type")->id(3)->insert([
    "title" => "Test document",
    "content" => "Sample content"
]);
     
# A new document will be inserted with _id = 3.
# [id is optional] if not specified, a unique hash key will be generated.
```

### Bulk insert a multiple of documents at once.

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

### Update an existing document
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

### Incrementing field
```php
ES::type("my_type")->id(3)->increment("views");
    
# Document has _id = 3 will be incremented by 1.

ES::type("my_type")->id(3)->increment("views", 3);

# Document has _id = 3 will be incremented by 3.

# [id is required]
```

### Decrementing field
```php
ES::type("my_type")->id(3)->decrement("views");
    
# Document has _id = 3 will be decremented by 1.
    
ES::type("my_type")->id(3)->decrement("views", 3);
    
# Document has _id = 3 will be decremented by 3.

# [id is required]
```

### Update using script
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

### Delete a document
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

Releases
--------
See the [release page](https://github.com/matchory/elasticsearch/releases).

Authors
-------
[Basem Khirat](http://basemkhirat.com) - [basemkhirat@gmail.com](mailto:basemkhirat@gmail.com) - [@basemkhirat](https://twitter.com/basemkhirat)  
[Moritz Friedrich](https://www.matchory.com) - [moritz@matchory.com](mailto:moritz@matchory.com)

Bugs, Suggestions and Contributions
-----------------------------------
Thanks to [everyone](https://github.com/basemkhirat/elasticsearch/graphs/contributors) who has contributed to the original project and
[everyone else](https://github.com/matchory/elasticsearch/graphs/contributors) who has contributed to this fork!  
Please use [Github](https://github.com/matchory/elasticsearch) for reporting bugs, and making comments or suggestions.

If you're interested in helping out, the most pressing issues would be modernizing the query builder to provide better support for Elasticsearch features as
well as completing the test suite!

License
-------
MIT

`Have a happy searching..`
