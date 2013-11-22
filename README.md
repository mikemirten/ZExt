##ZExt framework 1.0

###Requirements:

PHP 5.4 or later

###Installation:

1. Copy the ZExt dir from the library dir to a your project's library dir.
2. Add the ZExt namespace to a autoloader of a your project.

## Modules:

###Cache:

Working with a cache storages, namespaces, tags, backend/frontend architecture.
Memcache, Filesystem, Phalcon supporting.

###Config:

Configuration parameters for a project.
Ini with a sections, sections' inheritance, nested parameters, Json. 
Ini and Json writers.
Phalcon supporting.

###Dependencies injection

Locators and containers, the initializers by namespaces and class methods.
Phalcon supporting.

###Debug panel

PHP and a extensions versions, errors collectiong, memory usage, execution time, profiler aggregation.

###Profiler

Multipurpose profiler. Can be used with modules like a databases adapters.

###Helper

Helpers system for a modules like view or controller. Provides the helpers' broker, loader, "broker aware" trait.

###Validator

Validation module with package of an often used validators: alpha, alphanum, string length...

###Model

Models and collections system for an ORM and ODM with a data mapping of a query results to a models.
Model provides a data holding and accessing, validation, lazy initialization.
Collection provides a models spawning, aggregation functions, iteration.
