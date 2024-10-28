<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

final class GlobalsReader implements Reader
{
    /**
     * @return array<mixed>
     */
    public function read(): array
    {
        return $GLOBALS;
    }
}
