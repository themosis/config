<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Exceptions;

use RuntimeException;
use Themosis\Components\Config\Reader\ReaderKey;
use Throwable;

final class ReaderNotFound extends RuntimeException {
	public readonly ReaderKey $key;

	public function __construct(
		ReaderKey $key,
		string $message,
		int $code = 0,
		Throwable $previous = null,
	) {
		parent::__construct( $message, $code, $previous );

		$this->key = $key;
	}
}
