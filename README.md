<!--
SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>

SPDX-License-Identifier: GPL-3.0-or-later
-->

Themosis Config
===============

The Themosis configuration component provides a PHP API to manage application configuration from different sources.

Currently, the library comes with support for the following file types:

- PHP
- JSON

Besides using files as configuration sources, the library provides also the following sources support:

- PHP $_ENV
- PHP $GLOBALS

Installation
------------

Install the library using [Composer](https://getcomposer.org/):

```shell
composer require themosis/config
```

Usage
-----

The library exposes a `Configuration` interface and a concrete implementation `Config` class to let you access
configuration values from any declared sources.

Generally you declare a source `Reader` and pass it as a dependency to the `Config` concrete class. The `Config` class is
responsible to access the values returned by the reader.

> The `Config` instance is caching reader returned values at first `get()` method call.

### Read configuration from PHP

You can read configuration values declared in PHP files using the `PhpReader` class.
The PHP files are expected to return an array of configuration values.

```php
<?php
// File stored in /config/app.php
return [
    'name' => 'Themosis',
    'debug' => true,
    'wp' => [
        'home' => 'https://themosis.com',
        'site' => 'https://themosis.com/cms',
    ],
];
```

Then, in order to read configuration values, use the `get()` method with a dot syntax path as a first parameter:

```php
<?php

use Themosis\Components\Config\Config;
use Themosis\Components\Config\Reader\PhpReader;
use Themosis\Components\Filesystem\LocalFilesystem;

$reader = new PhpReader( new LocalFilesystem() );
$reader->from_file( __DIR__ . '/config/app.php' );

$config = new Config( $reader );

$config->get( 'name' ); // Themosis
$config->get( 'debug' ); // true
$config->get( 'wp.home' ); // https://themosis.com
$config->get( 'wp.site' ); // https://themosis.com/cms
```

### Read configuration from JSON

You can read configuration values from a JSON file by using the `JsonReader` class.

```json
{
    "name": "Themosis",
    "debug": true,
    "wp": {
        "home": "https://themosis.com",
        "site": "https://themosis.com/cms"
    }
}
```

```php
<?php

use Themosis\Components\Config\Config;
use Themosis\Components\Config\Reader\JsonReader;
use Themosis\Components\Filesystem\LocalFilesystem;

$reader = new JsonReader( new LocalFilesystem() );
$reader->from_file( __DIR__ . '/config/app.json' );

$config->get( 'name' ); // Themosis
$config->get( 'debug' ); // true
$config->get( 'wp.home' ); // https://themosis.com
$config->get( 'wp.site' ); // https://themosis.com/cms
```

### Read configuration files from directory

The library provides an `AggregateReader` class to let developers read configuration values
from multiple files stored in a directory.

```php
<?php

use Themosis\Components\Config\Config;
use Themosis\Components\Config\Reader\AggregateReader;
use Themosis\Components\Config\Reader\InMemoryReaders;
use Themosis\Components\Config\Reader\JsonReader;
use Themosis\Components\Config\Reader\PhpReader;
use Themosis\Components\Config\Reader\ReaderKey;
use Themosis\Components\Filesystem\LocalFilesystem;

$readers = new InMemoryReaders();
$readers->add( new ReaderKey( 'php' ), new PhpReader( new LocalFilesystem() ) );
$readers->add( new ReaderKey( 'json' ), new JsonReader( new LocalFilesystem() ) );

$reader = new AggregateReader(
    filesystem: new LocalFilesystem(),
    readers: $readers,
);
$reader->from_directory( __DIR__ . '/config' );

$config = new Config( $reader );
```

The above code snippet is setting up the Configuration instance with the AggregateReader.
The reader will look for configuration files stored in the `config` directory and will handle all
encountered `php` and `json` files.

By default, the AggregateReader can throw an `UnsupportedReader` exception if it tries to read a configuration
file not declared in the received `Readers` repository.

When reading the configuration values from the `AggregateReader`, the first element of the dot syntax path given while
calling the config `get()` method, is actually the configuration file name.

Here an example of a "config" directory structure:

```php
+-- config/
|   +-- app.php
|   +-- database.php
|   +-- global/
|       +-- styles.json
```

Based on above directory structure, you can retrieve configuration values like so:

```php
<?php

$reader = new AggregateReader(
    filesystem: new LocalFilesystem(),
    readers: $readers,
);

$reader->from_directory( __DIR__ . '/config' );

$config = new Config( $reader );

$config->get( 'app.name' ); // app.php => name: Themosis
$config->get( 'app.debug' );// app.php => debug: true

$config->get( 'database.connection.host' ); // database.php => connection => host: localhost

$config->get( 'styles.colors' ); // global => styles.json => colors: [[...]]
```

### Default configuration value

When using the config `get()` method, it is also possible to declare a default fallback value if the
requested configuration value is not found:

```php
<?php

use Themosis\Components\Config\Config;

$config = new Config( $reader );

$config->get( 'do-not-exist' ); // null
$config->get( 'do-not-exist', 'bar' ); // bar
$config->get( 'do-not-exist', true ); // true
$config->get( 'do-not-exist', 42 ); // 42
$config->get( 'do-not-exist', [1,2,3] ); // 42
```

> By default, the fallback value is `null`.
