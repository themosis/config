<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config;

use Themosis\Components\Config\Reader\Reader;

final class Config implements Configuration {
	/**
	 * @var array<mixed>
	 */
	private array $values;

	public function __construct(
		private Reader $reader,
	) {
	}

	public function get( ?string $path = null, mixed $fallback = null ): mixed {
		$array = $this->get_values_from_reader();

		if ( null === $path ) {
			return $array;
		}

		if ( isset( $array[ $path ] ) ) {
			return $array[ $path ];
		}

		if ( ! str_contains( $path, '.' ) ) {
			return $fallback;
		}

		foreach ( explode( '.', $path ) as $key ) {
			$key = is_numeric( $key ) ? (string) $key : $key;

			if ( is_array( $array ) && array_key_exists( $key, $array ) ) {
				$array = $array[ $key ];
			} else {
				return $fallback;
			}
		}

		return $array;
	}

	/**
	 * @return array<mixed>
	 */
	private function get_values_from_reader(): array {
		if ( ! isset( $this->values ) ) {
			$this->values = $this->reader->read();
		}

		return $this->values;
	}
}
