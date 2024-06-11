<?php

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

interface DirectorySource
{
    public function from_directory( string $directory_path ): void;
}
