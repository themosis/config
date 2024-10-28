<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

interface Readers
{
    public function add(ReaderKey $key, FileReader $reader): void;

    public function find(ReaderKey $key): FileReader;
}
