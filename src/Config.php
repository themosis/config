<?php

declare(strict_types=1);

namespace Themosis\Components\Config;

use Themosis\Components\Config\Reader\Reader;

final class Config implements Configuration {
	public function __construct(
		private Reader $reader,
	) {
	}

	public function get( ?string $path = null, mixed $fallback = null ): mixed {
		$array = $this->reader->read();

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
}