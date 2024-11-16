# Models

Each index type has a corresponding "Model" which is used to interact with that type.
Models allow you to query for data in your types or indices, as well as insert new documents into the type.


## Basic usage
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

## Retrieving Models

Once you have created a model and its associated index type, you are ready to start retrieving data from your index. For example:


```php
<?php

use App\Post;

$posts = App\Post::all();

foreach ($posts as $post) {
    echo $post->title;
}

```

## Adding Additional Constraints

The `all` method will return all of the results in the model's type. Each elasticsearch model serves as a query builder, you may also add constraints to queries, and then use the `get()` method to retrieve the results:

```php
$posts = App\Post::where('status', 1)
               ->orderBy('created_at', 'desc')
               ->take(10)
               ->get();

```


## Retrieving Single Models

```php
// Retrieve a model by document key...
$posts = App\Post::find("AVp_tCaAoV7YQD3Esfmp");
```


## Inserting Models


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

## Updating Models

The `save()` method may also be used to update models that already exist. To update a model, you should retrieve it, set any attributes you wish to update, and then call the save method.

```php
$post = App\Post::find(1);

$post->title = 'New Post Title';

$post->save();
```

## Deleting Models

To delete a model, call the `delete()` method on a model instance:

```php
$post = App\Post::find(1);

$post->delete();
```

## Query Scopes

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


## Accessors & Mutators

### Defining An Accessor
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

### Defining A Mutator

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



## Attribute Casting


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
