# Laravel Scout Integration

This package can be used as a Laravel Scout driver. Here's how to set it up and use it:

## Setup

1. First, follow the [Laravel Scout installation](https://laravel.com/docs/5.4/scout#installation) instructions.

2. Update your `config/scout.php` configuration file:

```php
# change the default driver to 'es'
	
'driver' => env('SCOUT_DRIVER', 'es'),
	
# link `es` driver with default elasticsearch connection in config/es.php
	
'es' => [
    'connection' => env('ELASTIC_CONNECTION', 'default'),
],
```

Have a look at [laravel Scout documentation](https://laravel.com/docs/5.4/scout#configuration).
