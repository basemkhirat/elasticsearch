# Installation

## Requirements

- `php` >= 5.6.6 
  
  See [Travis CI Builds](https://travis-ci.org/basemkhirat/elasticsearch).

- `laravel/laravel` >= 5.* or `laravel/lumen` >= 5.* or `composer application`

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
        ]
    ],
    'index' => 'my_index',
]);


# access the query builder using created connection

$documents = $connection->search("hello")->get();
```


