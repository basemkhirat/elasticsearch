# Commands

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

