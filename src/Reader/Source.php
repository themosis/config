<?php

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

interface Source {
	public function from_file( string $filepath ): void;
}
