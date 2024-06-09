<?php

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

use Themosis\Components\Filesystem\Filesystem;

final class PhpReader implements Reader {
	public function __construct(
		private Filesystem $filesystem,
	) {
	}

	public function read( string $filepath ): array {
		return $this->filesystem->require( $filepath );
	}
}
