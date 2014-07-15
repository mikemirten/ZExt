##ZExt framework 1.0

[![Build Status](https://travis-ci.org/mikemirten/ZExt.png?branch=master)](https://travis-ci.org/mikemirten/ZExt)

###Requirements

PHP 5.4 or later

###Installation

**Via Composer:**

Add to composer.json following strings:
```json
{
    "require": {
        "zext/zext": "dev-master"
    }
}
```

**Manually:**

1. Download the framework, unpack it and copy ZExt dir from library dir into your project's library dir.
2. Add the framework to autoload by namespace "ZExt" and dir "my_app_library/ZExt".

You can also use the framework's autoloader:
```php
require 'my_app_library/ZExt/Loader/Autoloader.php';

ZExt\Loader\Autoloader::registerDefaults();
```
