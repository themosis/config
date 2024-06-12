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
use Themosis\Components\Config\Reader\AggregateReader;
use Themosis\Components\Config\Reader\InMemoryReaders;
use Themosis\Components\Config\Reader\PhpReader;
use Themosis\Components\Config\Reader\ReaderKey;
use Themosis\Components\Filesystem\LocalFilesystem;

final class ConfigAggregatorTest extends TestCase {
	#[Test]
	public function it_can_generate_reader_key_without_illegal_characters(): void {
		$key = new ReaderKey( '   .php ' );
		$this->assertSame( 'php', (string) $key );

		$key = new ReaderKey( " inc.php\n" );
		$this->assertSame( 'inc.php', (string) $key );

		$key = new ReaderKey( ' .json ' );
		$this->assertSame( 'json', (string) $key );
	}

	#[Test]
	public function it_can_read_configuration_from_aggregated_files_in_a_directory(): void {
		$readers = new InMemoryReaders();
		$readers->add( new ReaderKey( 'php' ), new PhpReader( new LocalFilesystem() ) );

		$reader = new AggregateReader(
			filesystem: new LocalFilesystem(),
			readers: $readers,
		);

		$reader->from_directory( __DIR__ . '/fixtures/config' );

		$config = new Config( reader: $reader );

		$this->assertIsArray( $config->get() );
		$this->assertNotEmpty( $config->get() );

		$this->assertIsArray( $config->get( 'app' ) );
		$this->assertSame( 'Themosis', $config->get( 'app.name' ) );
		$this->assertIsArray( $config->get( 'app.wp' ) );
		$this->assertSame( 'http://themosis.com', $config->get( 'app.wp.home' ) );
		$this->assertSame( 'http://themosis.com/cms', $config->get( 'app.wp.siteurl' ) );
		$this->assertTrue( $config->get( 'app.debug' ) );

		$this->assertSame( 'sqlite', $config->get( 'database.default' ) );
		$this->assertIsArray( $config->get( 'database.connections' ) );
		$this->assertIsArray( $config->get( 'database.connections.sqlite' ) );
		$this->assertSame( 'sqlite', $config->get( 'database.connections.sqlite.driver' ) );
		$this->assertSame( ':memory:', $config->get( 'database.connections.sqlite.database' ) );
		$this->assertSame( '', $config->get( 'database.connections.sqlite.prefix' ) );
	}

	#[Test]
	public function it_can_throw_an_exception_if_invalid_directory(): void {
		$reader = new AggregateReader(
			filesystem: new LocalFilesystem(),
			readers: new InMemoryReaders(),
		);

		$this->expectException( InvalidConfigurationDirectory::class );

		$reader->from_directory( 'invalid-directory' );
	}

	#[Test]
	public function it_can_throw_an_exception_if_cannot_find_configuration_file_reader(): void {
		$reader = new AggregateReader(
			filesystem: new LocalFilesystem(),
			readers: new InMemoryReaders(),
		);

		$reader->from_directory( __DIR__ . '/fixtures/config' );

		$config = new Config( reader: $reader );

		$this->expectException( ReaderNotFound::class );

		$config->get( 'app.name' );
	}
}
