##Cache

Cache module provides a data caching service for an application. Supporting tags, namespaces, queries profiling, has backend/frontend architecture.

###Simple usage

Cache module has the cache factory, which creates combination of backend(s), decorators, factories, frontends by an input parameters.
The simpliest way to create a cache frontend is use the Factory with no parameters. By default will be used the Memcached server for a data storage
```php
<?php
use ZExt\Cache\Factory as CacheFactory;

$cache = CacheFactory::createFrontend();
```
