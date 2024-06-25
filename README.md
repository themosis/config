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
