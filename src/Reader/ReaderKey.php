<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

use Stringable;

final class ReaderKey implements Stringable {
	public function __construct(
		private string $file_extension,
	) {
		$this->file_extension = trim( $file_extension, " \n\r\t\v\0." );
	}

	public function equals( ReaderKey ...$others ): bool {
		foreach ( $others as $other ) {
			if ( $this->file_extension === $other->file_extension ) {
				return true;
			}
		}

		return false;
	}

	public function __toString(): string {
		return $this->file_extension;
	}
}
