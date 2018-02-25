# Change Log
All notable changes to this project will be documented in this file.

## [Released]

## [1.3] - 2017-10-23

# Added
- Auto package discovery for laravel 5.5 or higher.
- Custom connection handlers.

# Fixed
- Fix whereBetween and whereNotBetween methods.

## [1.2] - 2017-07-23

# Added
- New Elastic data model.

## [1.1] - 2017-05-16

# Fixed
- Fix command line for lumen.

## [1.0] - 2017-04-28

## [0.9.9] - 2017-04-27

# Fixed
- Allow more mapping settings in config file.

## [0.9.8] - 2017-04-01

# Fixed
- Fix callable checks for reserved php functions.


## [0.9.7] - 2017-04-01

# Added
- Add searchable weighted fields to search method.

# Removed
- Remove artisan commands for laravel 5.0.
- Remove wildcard query search for laravel scout.

## [0.9.6] - 2017-03-18

# Added
- Add _index to the result model.
- Improve bulk method to perform update and delete operations.


## [0.9.5] - 2017-03-08

# Added
- Add the body() query method.

## [0.9.4] - 2017-03-03

# Fixed
- Fix commands for lumen framework (working without facades).
- Fix indices list command for newer elasticsearch/elasticsearch package.

# Added
- Customization for reindex command: 
-- Add progressbar.
-- Add `--scroll` option to control  query scroll value.
-- Add `--hide-errors` option to hide reindexing errors.

# Updated
- Rename `--size` option to `--bulk-size` for reindexing command.


## [0.9.3] - 2017-02-27

# Fixed
- Optimize count() method.


## [0.9.2] - 2017-02-27

# Added
- Add bulk queries with different index or type names

# Fixed
- Add reindexing with '--skip-errors' option.
- Fix insert with no id.
- Fix query() method if no filter.


## [0.9.1] - 2017-02-26

# Added
- Add reindexing command with no downtime.
- Response method to get the native elasticserch response.

## [0.9] - 2017-02-25

# Fixed
- Fix index aliases update throw console.

## [0.8.9] - 2017-02-25

# Added
- Add index aliases using command line

# Updated
- Return data throw a model to avoid non exist property.

# Fixed
- Fix list indices command if no indices found.

## [0.8.8] - 2017-02-24

# Updated
- Update commands with a clear names.

## [0.8.7] - 2017-02-24

# Fixed
- Fix dynamic command connection option.

## [0.8.6] - 2017-02-24

### Added
- Add console environment support.
- Add query distance() method.
- Add check existence of an index.
- New bulk code style.

# Fixed
- Fix raw query example syntax in readme file.

# Removed
- Remove hhvm from Travis CI.

## [0.8.5] - 2017-02-19

### Fixed
- Fix some compatibility issues.

## [0.8.4] - 2017-02-19

### Fixed
- Fix pagination for laravel 5.1.

## [0.8.3] - 2017-02-19

### Fixed
- Fix laravel scout non resolved class.

## [0.8.2] - 2017-02-19

### Fixed
- Fix pagination for non-laravel apps.

## [0.8.1] - 2017-02-18

### Added
- Add lumen auto configuration.

## [0.8] - 2017-02-18

### Added
- Add laravel 5.* support.

### Removed
- Remove laravel scout. you should install it manually with this package if you want.

## [0.7.5] - 2017-02-17

### Fixed
- Fix query caching for lumen.

## [0.7.4] - 2017-02-17

### Added
- Add composer based applications support.

## [0.7.3] - 2017-02-16

### Added
- Add lumen framework support.

## [0.7.2] - 2017-02-14

### Added
- Make the package supports earlier requirements as much as possible.

## [0.7.1] - 2017-02-14

### Added
- Update dependencies of package to work with:
  
  php >= 5.6.6
  
  laravel/laravel >= 5.3
  
## [0.7] - 2017-02-12

### Added
- Add query caching layer.
- Add laravel scout support.
- Add query() method to get query before execution.
- Simplify readme docs.
- More optimization.

## [0.6] - 2017-02-04

### Added
- Add scan and scroll queries.
- Ignore some http request to avoid exceptions.

## [0.5] - 2017-01-17

### Added
- Add create index ability.
- Add drop index ability.
- Add mapping ability.
- Ignore HTTP response errors using query builder.

## [0.4] - 2017-01-15

### Added
- Add search boost factor.
- Add increment update.
- Add decrement update.
- Add update using script.

## [0.3] - 2017-01-07

### Added
- Add bulk inserts.

## [0.2] - 2017-01-07

### Added
- Some fixes.

## [0.1] - 2017-01-07

### Added
- Basic builder.


[Released]: https://github.com/basemkhirat/elasticsearch/compare/1.3...HEAD
[1.3]: https://github.com/basemkhirat/elasticsearch/compare/1.2...1.3
[1.2]: https://github.com/basemkhirat/elasticsearch/compare/1.1...1.2
[1.1]: https://github.com/basemkhirat/elasticsearch/compare/1.0...1.1
[1.0]: https://github.com/basemkhirat/elasticsearch/compare/0.9.9...1.0
[0.9.9]: https://github.com/basemkhirat/elasticsearch/compare/0.9.8...0.9.9
[0.9.8]: https://github.com/basemkhirat/elasticsearch/compare/0.9.7...0.9.8
[0.9.7]: https://github.com/basemkhirat/elasticsearch/compare/0.9.6...0.9.7
[0.9.6]: https://github.com/basemkhirat/elasticsearch/compare/0.9.5...0.9.6
[0.9.5]: https://github.com/basemkhirat/elasticsearch/compare/0.9.4...0.9.5
[0.9.4]: https://github.com/basemkhirat/elasticsearch/compare/0.9.3...0.9.4
[0.9.3]: https://github.com/basemkhirat/elasticsearch/compare/0.9.2...0.9.3
[0.9.2]: https://github.com/basemkhirat/elasticsearch/compare/0.9.1...0.9.2
[0.9.1]: https://github.com/basemkhirat/elasticsearch/compare/0.9...0.9.1
[0.9]: https://github.com/basemkhirat/elasticsearch/compare/0.8.9...0.9
[0.8.9]: https://github.com/basemkhirat/elasticsearch/compare/0.8.8...0.8.9
[0.8.8]: https://github.com/basemkhirat/elasticsearch/compare/0.8.7...0.8.8
[0.8.7]: https://github.com/basemkhirat/elasticsearch/compare/0.8.6...0.8.7
[0.8.6]: https://github.com/basemkhirat/elasticsearch/compare/0.8.5...0.8.6
[0.8.5]: https://github.com/basemkhirat/elasticsearch/compare/0.8.4...0.8.5
[0.8.4]: https://github.com/basemkhirat/elasticsearch/compare/0.8.3...0.8.4
[0.8.3]: https://github.com/basemkhirat/elasticsearch/compare/0.8.2...0.8.3
[0.8.2]: https://github.com/basemkhirat/elasticsearch/compare/0.8.1...0.8.2
[0.8.1]: https://github.com/basemkhirat/elasticsearch/compare/0.8...0.8.1
[0.8]: https://github.com/basemkhirat/elasticsearch/compare/0.7.5...0.8
[0.7.5]: https://github.com/basemkhirat/elasticsearch/compare/0.7.4...0.7.5
[0.7.4]: https://github.com/basemkhirat/elasticsearch/compare/0.7.3...0.7.4
[0.7.3]: https://github.com/basemkhirat/elasticsearch/compare/0.7.2...0.7.3
[0.7.2]: https://github.com/basemkhirat/elasticsearch/compare/0.7.1...0.7.2
[0.7.1]: https://github.com/basemkhirat/elasticsearch/compare/0.7...0.7.1
[0.7]: https://github.com/basemkhirat/elasticsearch/compare/0.6...0.7
[0.6]: https://github.com/basemkhirat/elasticsearch/compare/0.5...0.6
[0.5]: https://github.com/basemkhirat/elasticsearch/compare/0.4...0.5
[0.4]: https://github.com/basemkhirat/elasticsearch/compare/0.3...0.4
[0.3]: https://github.com/basemkhirat/elasticsearch/compare/0.2...0.3
[0.2]: https://github.com/basemkhirat/elasticsearch/compare/0.1...0.2
