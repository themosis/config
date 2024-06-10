<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Tests;

use PHPUnit\Framework\Attributes\Test;
use Themosis\Components\Config\Config;
use Themosis\Components\Config\Reader\EnvReader;

final class EnvConfigurationTest extends TestCase {
	#[Test]
	public function it_can_read_configuration_property_from_global_environment(): void {
		$_ENV['foo'] = 'bar';
		$_ENV['app'] = [
			'name'    => 'Themosis',
			'debug'   => true,
			'version' => 1.0,
		];

		$reader = new EnvReader();

		$config = new Config( reader: $reader );

		$this->assertSame( 'bar', $config->get( 'foo' ) );
		$this->assertIsArray( $config->get( 'app' ) );
		$this->assertSame( 'Themosis', $config->get( 'app.name' ) );
		$this->assertTrue( $config->get( 'app.debug' ) );
		$this->assertSame( 1.0, $config->get( 'app.version' ) );
	}

	#[Test]
	public function it_can_fallback_if_property_is_not_found(): void {
		$reader = new EnvReader();

		$config = new Config( reader: $reader );

		$this->assertNull( $config->get( 'foo' ) );
		$this->assertSame( 'bar', $config->get( 'foo', 'bar' ) );
		$this->assertTrue( $config->get( 'foo', true ) );
		$this->assertFalse( $config->get( 'foo', false ) );
	}
}
