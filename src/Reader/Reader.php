<?php

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

interface Reader {
	public function read(): array;
}
