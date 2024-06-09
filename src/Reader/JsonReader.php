<?php

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

use Themosis\Components\Filesystem\Filesystem;

final class JsonReader implements Reader {
	public function __construct(
		private Filesystem $filesystem,
	) {
	}

	public function read( string $filepath ): array {
		$json = $this->filesystem->read( $filepath );

		return json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
	}
}
