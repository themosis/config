<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

use Stringable;

final class ReaderKey implements Stringable
{
    public function __construct(
        private string $fileExtension,
    ) {
        $this->fileExtension = trim($fileExtension, " \n\r\t\v\0.");
    }

    public function equals(ReaderKey ...$others): bool
    {
        foreach ($others as $other) {
            if ($this->fileExtension === $other->fileExtension) {
                return true;
            }
        }

        return false;
    }

    public function __toString(): string
    {
        return $this->fileExtension;
    }
}
