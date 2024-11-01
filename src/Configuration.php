<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config;

interface Configuration
{
    public function get(?string $path = null, mixed $fallback = null): mixed;

    public function refresh(): Configuration;
}
