<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

use Themosis\Components\Config\Exceptions\ReaderNotFound;

final class InMemoryReaders implements Readers {
	private array $readers = [];

	public function add( ReaderKey $key, FileReader $reader ): void {
		$this->readers[ (string) $key ] = $reader;
	}

	public function find( ReaderKey $key ): FileReader {
		if ( ! isset( $this->readers[ (string) $key ] ) ) {
			throw new ReaderNotFound(
				message: sprintf( 'Reader not found for key with extension %s', (string) $key ),
			);
		}

		return $this->readers[ (string) $key ];
	}
}
