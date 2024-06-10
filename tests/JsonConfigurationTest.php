<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Tests;

use PHPUnit\Framework\Attributes\Test;
use Themosis\Components\Config\Config;
use Themosis\Components\Config\Exceptions\ConfigurationNotFound;
use Themosis\Components\Config\Exceptions\InvalidConfiguration;
use Themosis\Components\Config\Reader\JsonReader;
use Themosis\Components\Filesystem\LocalFilesystem;

final class JsonConfigurationTest extends TestCase {
	#[Test]
	public function it_can_read_configuration_from_a_json_file(): void {
		$reader = new JsonReader( new LocalFilesystem() );
		$reader->from_file( __DIR__ . '/fixtures/theme.json' );

		$config = new Config( reader: $reader );

		$this->assertSame( 'https://fake.themosis.com/', $config->get( 'schema' ) );
		$this->assertSame( 3, $config->get( 'version' ) );

		$this->assertIsArray( $config->get( 'settings' ) );
		$this->assertFalse( $config->get( 'settings.appearanceTools' ) );
		$this->assertIsArray( $config->get( 'settings.border' ) );
		$this->assertIsArray( $config->get( 'settings.color' ) );
		$this->assertTrue( $config->get( 'settings.color.background' ) );
		$this->assertTrue( $config->get( 'settings.color.custom' ) );
		$this->assertIsArray( $config->get( 'settings.color.palette' ) );
		$this->assertSame( '#ffffff', $config->get( 'settings.color.palette.0.color' ) );
		$this->assertSame( 'Base', $config->get( 'settings.color.palette.0.name' ) );
		$this->assertSame( 'base', $config->get( 'settings.color.palette.0.slug' ) );
		$this->assertSame( '#000000', $config->get( 'settings.color.palette.1.color' ) );
		$this->assertSame( 'Contrast', $config->get( 'settings.color.palette.1.name' ) );
		$this->assertSame( 'contrast', $config->get( 'settings.color.palette.1.slug' ) );
		$this->assertIsArray( $config->get( 'styles' ) );
		$this->assertSame( '#000000', $config->get( 'styles.color.text' ) );
		$this->assertSame( '#ffffff', $config->get( 'styles.color.background' ) );
		$this->assertSame( '#ffffff', $config->get( 'styles.elements.button.color.text' ) );
		$this->assertSame( '#000000', $config->get( 'styles.elements.button.color.background' ) );
		$this->assertIsArray( $config->get( 'customTemplates' ) );
		$this->assertIsArray( $config->get( 'templateParts' ) );
		$this->assertIsArray( $config->get( 'patterns' ) );
	}

	#[Test]
	public function it_can_not_read_configuration_if_file_does_not_exist(): void {
		$reader = new JsonReader( new LocalFilesystem() );
		$reader->from_file( 'path/does/not/exist.json' );

		$config = new Config( reader: $reader );

		$this->expectException( ConfigurationNotFound::class );

		$config->get();
	}

	#[Test]
	public function it_can_not_read_configuration_from_invalid_formatted_json(): void {
		$reader = new JsonReader( new LocalFilesystem() );
		$reader->from_file( __DIR__ . '/fixtures/invalid.json' );

		$config = new Config( reader: $reader );

		$this->expectException( InvalidConfiguration::class );

		$config->get();
	}
}
