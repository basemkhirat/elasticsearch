# Change Log
All notable changes to this project will be documented in this file.

## [Released]


## [0.9.1] - 2017-02-26

# Added
- add reindexing command with no downtime.
- response method to get the native elasticserch response.

## [0.9] - 2017-02-25

# Fixed
- fix index aliases update throw console.

## [0.8.9] - 2017-02-25

# Added
- add index aliases using command line

# Updated
- return data throw a model to avoid non exist property.

# Fixed
- fix list indices command if no indices found.

## [0.8.8] - 2017-02-24

# Updated
- update commands with a clear names.

## [0.8.7] - 2017-02-24

# Fixed
- fix dynamic command connection option.

## [0.8.6] - 2017-02-24

### Added
- add console environment support.
- Add query distance() method.
- add check existence of an index.
- new bulk code style.

# Fixed
- fix raw query example syntax in readme file.

# Removed
- remove hhvm from Travis CI.

## [0.8.5] - 2017-02-19

### Fixed
- fix some compatibility issues.

## [0.8.4] - 2017-02-19

### Fixed
- fix pagination for laravel 5.1.

## [0.8.3] - 2017-02-19

### Fixed
- fix laravel scout non resolved class.

## [0.8.2] - 2017-02-19

### Fixed
- fix pagination for non-laravel apps.

## [0.8.1] - 2017-02-18

### Added
- add lumen auto configuration.

## [0.8] - 2017-02-18

### Added
- add laravel 5.* support.

### Removed
- remove laravel scout. you should install it manually with this package if you want.

## [0.7.5] - 2017-02-17

### Fixed
- fix query caching for lumen.

## [0.7.4] - 2017-02-17

### Added
- add composer based applications support.

## [0.7.3] - 2017-02-16

### Added
- add lumen framework support.

## [0.7.2] - 2017-02-14

### Added
- make the package supports earlier requirements as much as possible.

## [0.7.1] - 2017-02-14

### Added
- update dependencies of package to work with:
  
  php >= 5.6.6
  
  laravel/laravel >= 5.3
  
## [0.7] - 2017-02-12

### Added
- add query caching layer.
- add laravel scout support.
- add query() method to get query before execution.
- simplify readme docs.
- more optimization.

## [0.6] - 2017-02-04

### Added
- add scan and scroll queries.
- ignore some http request to avoid exceptions.

## [0.5] - 2017-01-17

### Added
- add create index ability.
- add drop index ability.
- add mapping ability.
- ignore HTTP response errors using query builder.

## [0.4] - 2017-01-15

### Added
- add search boost factor.
- add increment update.
- add decrement update.
- add update using script.

## [0.3] - 2017-01-07

### Added
- add bulk inserts.

## [0.2] - 2017-01-07

### Added
- some fixes.

## [0.1] - 2017-01-07

### Added
- basic builder.


[Released]: https://github.com/basemkhirat/elasticsearch/compare/0.9.1...HEAD
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
