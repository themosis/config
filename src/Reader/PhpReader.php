<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

use Themosis\Components\Config\Exceptions\ConfigurationNotFound;
use Themosis\Components\Filesystem\Exceptions\InvalidFileException;
use Themosis\Components\Filesystem\Filesystem;

final class PhpReader implements Reader, Source {
	private string $filepath;

	public function __construct(
		private Filesystem $filesystem,
	) {
	}

	public function from_file( string $filepath ): void {
		$this->filepath = $filepath;
	}

	/**
	 * @return array<mixed>
	 */
	public function read(): array {
		$values = [];

		try {
			/** @var array<mixed> $values */
			$values = $this->filesystem->require( $this->filepath );
		} catch ( InvalidFileException $exception ) {
			throw new ConfigurationNotFound(
				message: sprintf( 'Configuration source not found at path %s', $this->filepath ),
				code: 0,
				previous: $exception,
			);
		}

		return $values;
	}
}
