<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Themosis\Components\Config\Exceptions\InvalidConfigurationDirectory;
use Themosis\Components\Config\Exceptions\ReaderNotFound;
use Themosis\Components\Config\Exceptions\UnsupportedReader;
use Themosis\Components\Filesystem\Filesystem;

final class AggregateReader implements DirectoryReader {
	private string $directory_path;

	/**
	 * @var array<int,ReaderKey>
	 */
	private array $readers_to_ignore = [];

	public function __construct(
		private Filesystem $filesystem,
		private Readers $readers,
	) {
	}

	public function ignore_reader( ReaderKey $key ): self {
		$this->readers_to_ignore[] = $key;

		return $this;
	}

	public function from_directory( string $directory_path ): void {
		if ( ! $this->filesystem->is_directory( $directory_path ) ) {
			throw new InvalidConfigurationDirectory(
				message: sprintf( 'Invalid directory path given: %s', $directory_path ),
			);
		}

		$this->directory_path = $directory_path;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function read(): array {
		$values = [];

		$iterator = new RecursiveIteratorIterator(
			iterator: new RecursiveDirectoryIterator(
				directory: $this->directory_path,
				flags: FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::SKIP_DOTS,
			),
			mode: RecursiveIteratorIterator::LEAVES_ONLY,
		);

		foreach ( $iterator as $filepath => $file ) {
			/**
			 * @var string $filepath
			 * @var SplFileInfo $file
			 */
			$basename = pathinfo( $file->getFilename(), PATHINFO_FILENAME );

			try {
				$reader = $this->readers->find( new ReaderKey( $file->getExtension() ) );
			} catch ( ReaderNotFound $exception ) {
				if ( $this->reader_should_be_ignored( $exception->key ) ) {
					continue;
				}

				throw new UnsupportedReader(
					message: sprintf( 'Unsupported configuration file found in aggregate reader: %s', (string) $exception->key ),
					code: 0,
					previous: $exception,
				);
			}

			$reader->from_file( $filepath );

			$values[ $basename ] = $reader->read();
		}

		return $values;
	}

	private function reader_should_be_ignored( ReaderKey $key ): bool {
		return $key->equals( ...$this->readers_to_ignore );
	}
}
