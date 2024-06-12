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
use Themosis\Components\Filesystem\Filesystem;

final class AggregateReader implements DirectoryReader {
	private string $directory_path;

	public function __construct(
		private Filesystem $filesystem,
		private Readers $readers,
	) {
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
			$reader   = $this->readers->find( new ReaderKey( $file->getExtension() ) );
			$reader->from_file( $filepath );

			$values[ $basename ] = $reader->read();
		}

		return $values;
	}
}
