<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Tests;

use PHPUnit\Framework\Attributes\Test;
use Themosis\Components\Config\Config;
use Themosis\Components\Config\Exceptions\ConfigurationNotFound;
use Themosis\Components\Config\Reader\PhpReader;
use Themosis\Components\Filesystem\LocalFilesystem;

final class PhpConfigurationTest extends TestCase {
	#[Test]
	public function it_can_read_configuration_from_a_php_file(): void {
		$reader = new PhpReader( new LocalFilesystem() );
		$reader->from_file( __DIR__ . '/fixtures/app.php' );

		$config = new Config( reader: $reader );

		$this->assertSame( 'Themosis', $config->get( 'name' ) );

		$this->assertSame( 'http://themosis.com', $config->get( 'wp.home' ) );
		$this->assertSame( 'http://themosis.com/cms', $config->get( 'wp.site' ) );

		$this->assertTrue( $config->get( 'debug' ) );

		$this->assertSame( 'hjkl', $config->get( 'salts.auth_key' ) );
		$this->assertSame( 'hjkl', $config->get( 'salts.secure_auth_key' ) );
		$this->assertSame( 'hjkl', $config->get( 'salts.logged_in_key' ) );
		$this->assertSame( 'hjkl', $config->get( 'salts.nonce_key' ) );
		$this->assertSame( 'hjkl', $config->get( 'salts.auth_salt' ) );
		$this->assertSame( 'hjkl', $config->get( 'salts.secure_auth_salt' ) );
		$this->assertSame( 'hjkl', $config->get( 'salts.logged_in_salt' ) );
		$this->assertSame( 'hjkl', $config->get( 'salts.nonce_salt' ) );
	}

	#[Test]
	public function it_can_read_all_properties_if_no_path_is_given(): void {
		$reader = new PhpReader( new LocalFilesystem() );
		$reader->from_file( __DIR__ . '/fixtures/app.php' );

		$config = new Config( reader: $reader );

		$this->assertTrue( is_array( $config->get() ) );

		$expected = [
			'name'  => 'Themosis',
			'wp'    => [
				'home' => 'http://themosis.com',
				'site' => 'http://themosis.com/cms',
			],
			'debug' => true,
			'salts' => [
				'auth_key'         => 'hjkl',
				'secure_auth_key'  => 'hjkl',
				'logged_in_key'    => 'hjkl',
				'nonce_key'        => 'hjkl',
				'auth_salt'        => 'hjkl',
				'secure_auth_salt' => 'hjkl',
				'logged_in_salt'   => 'hjkl',
				'nonce_salt'       => 'hjkl',
			],
		];

		$this->assertSame( $expected, $config->get() );
	}

	#[Test]
	public function it_can_use_fallback_value_if_property_path_does_not_exist(): void {
		$reader = new PhpReader( new LocalFilesystem() );
		$reader->from_file( __DIR__ . '/fixtures/app.php' );

		$config = new Config( reader: $reader );

		$this->assertNull( $config->get( 'foo' ) );
		$this->assertSame( 'bar', $config->get( 'foo', 'bar' ) );
		$this->assertFalse( $config->get( 'foo', false ) );
	}

	#[Test]
	public function it_can_not_read_configuation_from_non_existing_file(): void {
		$reader = new PhpReader( new LocalFilesystem() );
		$reader->from_file( '/path/to/nowhere' );

		$config = new Config( reader: $reader );

		$this->expectException( ConfigurationNotFound::class );

		$config->get();
	}
}
