<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Tests;

use PHPUnit\Framework\Attributes\Test;
use Themosis\Components\Config\Config;
use Themosis\Components\Config\Exceptions\InvalidConfigurationDirectory;
use Themosis\Components\Config\Exceptions\ReaderNotFound;
use Themosis\Components\Config\Exceptions\UnsupportedReader;
use Themosis\Components\Config\Reader\AggregateReader;
use Themosis\Components\Config\Reader\InMemoryReaders;
use Themosis\Components\Config\Reader\JsonReader;
use Themosis\Components\Config\Reader\PhpReader;
use Themosis\Components\Config\Reader\ReaderKey;
use Themosis\Components\Filesystem\LocalFilesystem;

final class ConfigAggregatorTest extends TestCase
{
    #[Test]
    public function it_can_generate_reader_key_without_illegal_characters(): void
    {
        $key = new ReaderKey('   .php ');
        $this->assertSame('php', (string) $key);

        $key = new ReaderKey(" inc.php\n");
        $this->assertSame('inc.php', (string) $key);

        $key = new ReaderKey(' .json ');
        $this->assertSame('json', (string) $key);
    }

    #[Test]
    public function it_can_read_configuration_from_aggregated_files_in_a_directory(): void
    {
        $readers = new InMemoryReaders();
        $readers->add(new ReaderKey('php'), new PhpReader(new LocalFilesystem()));

        $reader = new AggregateReader(
            filesystem: new LocalFilesystem(),
            readers: $readers,
        );

        $reader->fromDirectory(__DIR__ . '/fixtures/config');

        $config = new Config(reader: $reader);

        $this->assertIsArray($config->get());
        $this->assertNotEmpty($config->get());

        $this->assertIsArray($config->get('app'));
        $this->assertSame('Themosis', $config->get('app.name'));
        $this->assertIsArray($config->get('app.wp'));
        $this->assertSame('http://themosis.com', $config->get('app.wp.home'));
        $this->assertSame('http://themosis.com/cms', $config->get('app.wp.siteurl'));
        $this->assertTrue($config->get('app.debug'));

        $this->assertSame('sqlite', $config->get('database.default'));
        $this->assertIsArray($config->get('database.connections'));
        $this->assertIsArray($config->get('database.connections.sqlite'));
        $this->assertSame('sqlite', $config->get('database.connections.sqlite.driver'));
        $this->assertSame(':memory:', $config->get('database.connections.sqlite.database'));
        $this->assertSame('', $config->get('database.connections.sqlite.prefix'));
    }

    #[Test]
    public function it_can_throw_an_exception_if_invalid_directory(): void
    {
        $reader = new AggregateReader(
            filesystem: new LocalFilesystem(),
            readers: new InMemoryReaders(),
        );

        $this->expectException(InvalidConfigurationDirectory::class);

        $reader->fromDirectory('invalid-directory');
    }

    #[Test]
    public function it_can_throw_an_exception_if_cannot_find_configuration_file_reader(): void
    {
        $reader = new AggregateReader(
            filesystem: new LocalFilesystem(),
            readers: new InMemoryReaders(),
        );

        $reader->fromDirectory(__DIR__ . '/fixtures/config');

        $config = new Config(reader: $reader);

        $this->expectException(UnsupportedReader::class);

        $config->get('app.name');
    }

    #[Test]
    public function it_can_aggregate_configuration_from_php_and_json_files_and_fail_on_license_files(): void
    {
        $readers = new InMemoryReaders();
        $readers->add(new ReaderKey('php'), new PhpReader(new LocalFilesystem()));
        $readers->add(new ReaderKey('json'), new JsonReader(new LocalFilesystem()));

        $reader = new AggregateReader(
            filesystem: new LocalFilesystem(),
            readers: $readers,
        );

        $reader->fromDirectory(__DIR__ . '/fixtures/config-all');

        $config = new Config(reader: $reader);

        $this->expectException(UnsupportedReader::class);

        $config->get('app.name');
    }

    #[Test]
    public function it_can_aggregate_configuration_from_php_and_json_files_while_ignoring_license_files(): void
    {
        $readers = new InMemoryReaders();
        $readers->add(new ReaderKey('php'), new PhpReader(new LocalFilesystem()));
        $readers->add(new ReaderKey('json'), new JsonReader(new LocalFilesystem()));

        $reader = new AggregateReader(
            filesystem: new LocalFilesystem(),
            readers: $readers,
        );

        $reader->ignoreReader(new ReaderKey('license'));

        $reader->fromDirectory(__DIR__ . '/fixtures/config-all');

        $config = new Config(reader: $reader);

        $this->assertSame('Themosis', $config->get('app.name'));
        $this->assertTrue($config->get('app.debug'));

        $this->assertSame('Theme', $config->get('theme.name'));
        $this->assertSame('A theme configuration.', $config->get('theme.description'));
        $this->assertSame(3, $config->get('theme.version'));
        $this->assertFalse($config->get('theme.settings.appearanceTools'));

        $this->assertIsArray($config->get('global.styles'));
        $this->assertSame('Primary', $config->get('global.styles.styles.colors.0.name'));
        $this->assertSame('#3490dc', $config->get('global.styles.styles.colors.0.color'));
        $this->assertSame('primary', $config->get('global.styles.styles.colors.0.slug'));

        $this->assertIsArray($config->get('global.themes'));
        $this->assertIsArray($config->get('global.themes.menu'));
        $this->assertSame('Main Menu', $config->get('global.themes.menu.primary'));
        $this->assertSame('Secondary Menu', $config->get('global.themes.menu.sidebar'));
    }
}
