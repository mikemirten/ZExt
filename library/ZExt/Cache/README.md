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
d
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

* `__construct(BackendInterface $backend = null, string $namespace = null)`

Constructor. Backend can be passed through it. Also a namespace can be specified.
You can specify the unique namespace for an IDs for the frontend
* `void setBackend(BackendInterface $backend)`

Backend can be supplied anytime after instantiating.

* `void setNamespace(string $namespace)`

You can specify the unique namespace for an IDs for the frontend

* `void setDefaultLifetime(int $lifetime)`

Default data lifetime in a cache in seconds

* `bool set(string $id, mixed $data, int $lifetime = null, string | array $tags = null)`

Store the data in the cache. Can be specified the tag(s). Exception will be thrown if a backend is not taggable. Boolean will be returned: true - if succeded, false - if not.

* `bool setMany(array $data, int $lifetime = null, $tags = null)`

The same as `set()`, but for the many of a data. Key of the data array as IDs, value as a data.

* `mixed get(string $id)`

Get the data from the cache by the id. Null will be returned if no the data exists in the cache.

* `array getMany(array $id)`

The same as `get()`, but for the many of a data. Returned array will be contain the existing data: key of the array as IDs, value as a data.

* `array getByTag(string | array $tags, bool $intersect = false)`

The same as `getMany()`, but by the tag(s) instead of IDs. The "intersect" option finds the data, which contain all the specified tags, if = true, otherwise all the data, which contain one of the specified tags. Exception will be thrown if a backend is not taggable.

* `bool has(string $id)`

Has the data with the id in the cache.

* `bool remove(string $id)`

Remove the data from the cache. False will be returned if the data have not exist in the cache.

* `bool removeMany(array $id)`

The same as `remove`, but for the many of a data.

* `bool removeByTag(string | array $tags, $intersect = false)`

The sane as `getByTag`, but data will be removed instead of obtained

* `bool inc(string $id, int $value = 1)`

Increment the integer value, which stored in the cache, by the specified value if one specified

* `bool dec(string $id, int $value = 1)`

The same as `inc` but decrement by the value.

Frontend also works with the "magic" methods: `__set()` as `set()`, `__get()` as `get()`, `__isset()` as `has()`, `__unset()` as `remove()`.

###Factory of a frontends

Instead of direct creating the frontend, you can create the Factory, which will be used for creation of a frontends in a many places of your application. It is the more suitable way for a complex applications, because you can separate the namespaces of IDs stored in a different services, and use the most suitable frontends for a different cases.

Factory able to create the next type of frontends:

* `Wrapper createWrapper(string $namespace)`

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
