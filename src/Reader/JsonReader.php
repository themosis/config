<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

use JsonException;
use Themosis\Components\Config\Exceptions\ConfigurationNotFound;
use Themosis\Components\Config\Exceptions\InvalidConfiguration;
use Themosis\Components\Filesystem\Exceptions\FileDoesNotExist;
use Themosis\Components\Filesystem\Exceptions\ReadFileException;
use Themosis\Components\Filesystem\Filesystem;

final class JsonReader implements FileReader {
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
		$json   = '';

		try {
			$json = $this->filesystem->read( $this->filepath );
		} catch ( FileDoesNotExist $exception ) {
			throw new ConfigurationNotFound(
				message: sprintf( 'JSON configuration source not found at path %s', $this->filepath ),
				code: 0,
				previous: $exception,
			);
		} catch ( ReadFileException $exception ) {
			throw new InvalidConfiguration(
				message: 'Invalid JSON configuration file content.',
				code: 1,
				previous: $exception,
			);
		}

		try {
			/** @var array<mixed> $values */
			$values = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
		} catch ( JsonException $exception ) {
			throw new InvalidConfiguration(
				message: 'Invalid JSON structure.',
				code: 2,
				previous: $exception,
			);
		}

		return $values;
	}
}
