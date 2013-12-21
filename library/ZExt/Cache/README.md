##Cache

Cache module provides a data caching service for an application. Supporting tags, namespaces, queries profiling, has backend/frontend architecture.

###Simple usage

Cache module has the cache factory, which creates combination of backend(s), decorators, factories, frontends by an input parameters.
The simpliest way to create a cache frontend is use the Factory with no parameters. By default will be used the Memcached server for a data storage.
```php
<?php
use ZExt\Cache\Factory as CacheFactory;

$cache = CacheFactory::createFrontend();
```
Factory can be used with a parameters passed as an array or a Traversable implementation (eg. ZExt\Config) with keys as the param name and values as the param value.

**Parameters can be in the "camelCase" or the "underscore" format, it is does not matter. Eg. `tags_backend` and `tagsBackend` is all the same.**

| Param name  | Datatype | Default | Description |
|:------------|:---------|:--------|:------------|
|type         | string   | memcache       | Type of the backend (memcache, files...)|
|profiler     | bool     | false          | Queries must be profileable (implements the "ZExt\Profiler\ProfileableInterface")|
|tags         | bool     | false          | Backend must support the operations with a tags (save with tags, get by tags...)|
|tags_backend | array    | null           | Tags must be stored in a separated backend with personal params (regardless of tags supporting by backend)|
|serialize    | string   | null           | Serialize a data (eg. into json)|

```php
<?php
use ZExt\Cache\Factory as CacheFactory;

$cache = CacheFactory::createFrontend([
    'type' => 'file'
]);
```

###Usage with a config file

You can place your cache parameters in an application config.

```php
<?php
use ZExt\Config\Factory as ConfigFactory;
use ZExt\Cache\Factory  as CacheFactory;

$config = ConfigFactory::createFromFile('application.ini');
$cache  = CacheFactory::createFrontend($config->cache);
```
application.ini
```ini
cache.type = file
```

###Tags

Data can be marked by a tag or by the set of a tags. Tags provides the able to perform the operations with a sets of a data, such as: find by a tag(s) or remove by a tag(s).

Some cache backend adapters can provide the ability of using a tags. Those which can not, can be decorated by the Taggable decorator, to get the ability of using a tags. The "TaggableInterface" implemented by a backend says, that the tags ability is present.

If the tags ability is required in the your application, just set the "tags" option into the "true" (or "On" for an ini-config).
You can also specify a separate backend for a tags storage.

Example with three memcached servers, two for a data storage, the dedicated one for a tags:
```php
<?php
$cacheConfig = [
    'type'    => 'memcache',
    'tags'    => true,
    'servers' => [
        // Both servers at the same host (localhost in the our case)
        ['port' => 11211],
        ['port' => 11212]
    ],
    'tags_backend' => [
        'type'    => 'memcache',
        'servers' => [
            // Dedicated memcached server for a tags storage
            ['port' => 11213]
        ]
    ]
];
```

For the ini:
```ini
cache.type           = memcache
cache.tags           = On
cache.servers.0.port = 11211
cache.servers.1.port = 11212

cache.tags_backend.type           = memcache
cache.tags_backend.servers.0.port = 11213
```

###Frontend

Frontend is the final interface of a cache usage.
List of the provided methods:

```java
void __construct(BackendInterface $backend = null, string $namespace = null)
```

Constructor. Backend can be passed through it. Also a namespace can be specified.
You can specify the unique namespace for an IDs for the frontend

```java
void setBackend(BackendInterface $backend)
```

Backend can be supplied anytime after instantiating.

```java
void setNamespace(string $namespace)
```

You can specify the unique namespace for an IDs for the frontend.

```java
void setDefaultLifetime(int $lifetime)
```

Default data lifetime in a cache in seconds

```java
boolean set(string $id, mixed $data, int $lifetime = null, string | array $tags = null)
```

Store the data in the cache. Can be specified the tag(s). Exception will be thrown if a backend is not taggable. Boolean will be returned: true - if succeded, false - if not.

```java
boolean setMany(array $data, int $lifetime = null, string | array $tags = null)
```

The same as `set()`, but for the many of a data. Key of the data array as IDs, value as a data.

```java
mixed get(string $id)
```

Get the data from the cache by the id. Null will be returned if no the data exists in the cache.

```java
array getMany(array $id)
```

The same as `get()`, but for the many of a data. Returned array will be contain the existing data: key of the array as IDs, value as a data.

```java
array getByTag(string | array $tags, boolean $intersect = false)
```

The same as `getMany()`, but by the tag(s) instead of IDs. The "intersect" option finds the data, which contain all the specified tags, if = true, otherwise all the data, which contain one of the specified tags. Exception will be thrown if a backend is not taggable.

```java
boolean has(string $id)
```

Has the data with the id in the cache.

```java
boolean remove(string $id)
```

Remove the data from the cache. False will be returned if the data have not exist in the cache.

```java
boolean removeMany(array $id)
```

The same as `remove`, but for the many of a data.

```java
boolean removeByTag(string | array $tags, $intersect = false)
```

The sane as `getByTag`, but data will be removed instead of obtained

```java
boolean inc(string $id, int $value = 1)
```

Increment the integer value, which stored in the cache, by the specified value if one specified

```java
boolean dec(string $id, int $value = 1)
```

The same as `inc` but decrement by the value.

Frontend also works with the "magic" methods: `__set()` as `set()`, `__get()` as `get()`, `__isset()` as `has()`, `__unset()` as `remove()`.

###Factory of a frontends

Instead of direct creating the frontend, you can create the Factory, which will be used for creation of a frontends in a many places of your application. It is the more suitable way for a complex applications, because you can separate the namespaces of IDs stored in a different services, and use the most suitable frontends for a different cases.

Factory able to create the next type of frontends:

```java
Wrapper createWrapper(string $namespace)
```

Simple frontend, which works with the specified namespace of an IDs  of a cache.

Creation of a factory not harder than creation of a frontend:
```php
<?php
$frontendFactory = CacheFactory::createFrontendFactory($options);
```
instead of:
```php
<?php
$frontend = CacheFactory::createFrontend($options);
```

Further you just call to a factory method `createWrapper(string $namespace)` for creation of a frontend with the unique namespace:

```php
<?php
use ZExt\Cache\Frontend\FactoryInterface as CacheFactoryInterface;

abstract class ServiceAbstract {

    protected $cache;

    public function __construct(CacheFactoryInterface $cacheFactory) {
        $serviceName = $this->getServiceName();
        $this->cache = $cacheFactory->createWrapper($serviceName);
    }

    protected function getServiceName();

}

class ProductsService extends ServiceAbstract {

    protected function getServiceName() {
        return 'products';
    }

    public function getProductById($id) {
        $product = $this->cache->get($id);

        if ($product === null) {
           // Get the product from a database
           // ...

           $this->cache->set($id, $product);
        }

        return $product;
    }

}
```

##Backends

###Memcache

Backend for the one of the most popular cache system.
Accepted parameters for a direct instance or creating through the factory:

| Param name          | Datatype | Default | Description |
|:--------------------|:---------|:--------|:------------|
| servers             | array    | null    | Memcache servers params |
| namespace           | string   | null    | Namespace of an IDs |
| compression         | bool     | false   | Use compression of a data |
| operationExceptions | bool     | true    | Throw the exceptions by an operation errors |
| client              | Memcache | null    | Configured memcache client instance if necessary |

Server parameters:

| Param name    | Datatype | Default     | Description |
|:--------------|:---------|:------------|:------------|
| host          | string   | '127.0.0.1' | IP address or the host name or the socket path |
| port          | int      | 11211       | TCP port number |
| persistent    | bool     | true        | Persistent connection (Will not be closed on a script end, and can be reused) |
| weight        | int      | 1           | Server weight in the servers pool |
| timeout       | int      | 1           | Connection timeout in seconds |
| retryInterval | int      | 15          | Connection retry interval in seconds |

Instance example:
```php
<?php
use ZExt\Cache\Backend\Memcache;

$backend = new Memcache([
    'namespace'   => 'my_app',
    'compression' => true,
    'servers'     => [
        ['host' => '192.168.1.20'],
        ['host' => '192.168.1.21']
    ]
]);
```
Through the factory:
```php
<?php
use ZExt\Cache\Factory as CacheFactory;

$cache = CacheFactory::createFrontend([
    'type'        => 'memcache',
    'namespace'   => 'my_app',
    'compression' => true,
    'servers'     => [
        ['host' => '192.168.1.20'],
        ['host' => '192.168.1.21']
    ]
]);
```
Through the INI config:
```ini
cache.type           = memcache
cache.namespace      = my_app
cache.compression    = On
cache.servers.0.host = 192.168.1.20
cache.servers.1.host = 192.168.1.20
```

###File

The File backend uses the file system for storage the data. Apparently this way not suitable for a highly loaded projects, but can be suitable for a small projects, auxiliary and development purposes.

Accepted parameters:

| Param name          | Datatype | Default     | Description |
|:--------------------|:---------|:------------|:------------|
| cachePath           | string   | system temp | Path to the cache directory |
| cachePrefix         | string   | 'zcache'    | Prefix for the cache filenames |
| compression         | bool     | true        | Use compression of a data |
| compressionTreshold | int      | 1024        | Compression theshold in bytes |
| compressionLevel    | int      | 1           | Compression level 1-9 (higher -> better compression, slowly operations) |

Instance example:
```php
<?php
use ZExt\Cache\Backend\File;

$backend = new File([
    'cache_path'   => '/my_app/tmp',
    'cache_prefix' => 'my_prefix'
]);
```Instance example:
```php
<?php
use ZExt\Cache\Backend\File;

$backend = new File([
    'cache_path'   => '/my_app/tmp',
    'cache_prefix' => 'my_prefix'
]);
```
Through the factory:
```php
<?php
use ZExt\Cache\Factory as CacheFactory;

$cache = CacheFactory::createFrontend([
    'type'         => 'file',
    'cache_path'   => '/my_app/tmp',
    'cache_prefix' => 'my_prefix'
]);
```
Through the INI config:
```ini
cache.type         = file
cache.cache_path   = /my_app/tmp
cache.cache_prefix = my_prefix
```
Through the factory:
```php
<?php
use ZExt\Cache\Factory as CacheFactory;

$cache = CacheFactory::createFrontend([
    'type'         => 'file',
    'cache_path'   => '/my_app/tmp',
    'cache_prefix' => 'my_prefix'
]);
```
Through the INI config:
```ini
cache.type         = file
cache.cache_path   = /my_app/tmp
cache.cache_prefix = my_prefix
```

###Dummy

The "dummy" backend does nothing and accepts no parameters. The only reason of using it is development and testing.

Instance example:
```php
<?php
use ZExt\Cache\Backend\Dummy;

$backend = new Dummy();
```
Through the factory:
```php
<?php
use ZExt\Cache\Factory as CacheFactory;

$cache = CacheFactory::createFrontend([
    'type' => 'dummy'
]);
```
Through the INI config:
```ini
cache.type = dummy
```

###Phalcon cache aggregation

The backend provides aggregation with the [Phalcon framework](http://phalconphp.com/) cache module. It can be useful in the case if you already uses the Phalcon in your application.

Accepted parameters:

| Param name          | Datatype         | Default | Description |
|:--------------------|:-----------------|:--------|:------------|
| namespace           | string           | null    | Namespace of an IDs |
| operationExceptions | bool             | true    | Throw the exceptions by an operation errors |
| backend             | BackendInterface | null    | Configured Phalcon backend instance |

Instance example:
```php
<?php
use Phalcon\Cache\Frontend\Data    as PhalconFrontend;
use Phalcon\Cache\Backend\Memcache as PhalconMemcache;
use ZExt\Cache\Backend\PhalconWrapper;

$phalconBackend = new PhalconFrontend([
    'lifetime' => 3600
]);

$phalconCache = new PhalconMemcache($phalconBackend, [
    'host'       => 'localhost',
    'port'       => 11211,
    'persistent' => true
]);

$backend = new PhalconWrapper($phalconCache, [
    'namespace' => 'my_app'
]);
```

Through the factory:
```php
<?php
use Phalcon\Cache\Frontend\Data    as PhalconFrontend;
use Phalcon\Cache\Backend\Memcache as PhalconMemcache;
use ZExt\Cache\Factory             as CacheFactory;

$phalconBackend = new PhalconFrontend([
    'lifetime' => 3600
]);

$phalconCache = new PhalconMemcache($phalconBackend, [
    'host'       => 'localhost',
    'port'       => 11211,
    'persistent' => true
]);

$cache = CacheFactory::createFrontend([
    'type'    => 'phalconWrapper',
    'backend' => $phalconCache,
    'tags'    => true
]);
```
