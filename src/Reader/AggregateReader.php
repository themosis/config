<?php

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

use Themosis\Components\Filesystem\Filesystem;

final class AggregateReader implements Reader, DirectorySource
{
    private string $directory_path;

    public function __construct(
        private Filesystem $filesystem,
        private iterable $readers,
    ) {
    }

    public function from_directory(string $directory_path): void {
    
        if (! $this->filesystem->is_directory( $directory_path )) {
            // TODO: throw exception and complain about invalid directory path?
        }

        $this->directory_path = $directory_path;
    }

    public function read(): array {
        // 1. Loop through all files in the directory.
        // 2. Call the associated reader on each file and retrieve the values.
        // 3. Get file basename as key.
        // 4. Merge configuration values under the file basename key.
        // 5. Return the concatenated configuration values.
        $values = [];

        // TODO: experiment with SPL RecursiveDirectoryIterator, ..
        // TODO: verify glob flags usage
        foreach ( glob( $this->directory_path ) as $filename ) {
            $extension = pathinfo( $filename, PATHINFO_EXTENSION );
            $basename = basename( $filename, ".{$extension}" ); 

            // Raw test for handling readers... would need some sort of specification to resolve them based on extension...
            foreach ( $this->readers as $reader ) {
                if ( $extension === 'php' && $reader instanceof PhpReader ) {
                    $reader->from_file( $filename );
                    $values[$basename] = $reader->read();
                }
            }
        }

        return $values;
    }
}
