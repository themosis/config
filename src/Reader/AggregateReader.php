<?php

// SPDX-FileCopyrightText: 2024 Julien Lambé <julien@themosis.com>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Themosis\Components\Config\Reader;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Themosis\Components\Config\Exceptions\InvalidConfigurationDirectory;
use Themosis\Components\Config\Exceptions\ReaderNotFound;
use Themosis\Components\Config\Exceptions\UnsupportedReader;
use Themosis\Components\Filesystem\Filesystem;

final class AggregateReader implements DirectoryReader
{
    private string $directoryPath;

    /**
     * @var array<int,ReaderKey>
     */
    private array $readersToIgnore = [];

    public function __construct(
        private Filesystem $filesystem,
        private Readers $readers,
    ) {
    }

    public function ignoreReader(ReaderKey $key): self
    {
        $this->readersToIgnore[] = $key;

        return $this;
    }

    public function fromDirectory(string $directoryPath): void
    {
        if (! $this->filesystem->isDirectory($directoryPath)) {
            throw new InvalidConfigurationDirectory(
                message: sprintf('Invalid directory path given: %s', $directoryPath),
            );
        }

        $this->directoryPath = $directoryPath;
    }

    /**
     * @return array<string, mixed>
     */
    public function read(): array
    {
        $values = [];

        $iterator = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(
                directory: $this->directoryPath,
                flags: FilesystemIterator::CURRENT_AS_FILEINFO
                    | FilesystemIterator::KEY_AS_PATHNAME
                    | FilesystemIterator::SKIP_DOTS,
            ),
            mode: RecursiveIteratorIterator::LEAVES_ONLY,
        );

        foreach ($iterator as $filepath => $file) {
            /**
             * @var string $filepath
             * @var SplFileInfo $file
             */
            try {
                $reader = $this->readers->find(new ReaderKey($file->getExtension()));
            } catch (ReaderNotFound $exception) {
                if ($this->readerShouldBeIgnored($exception->key)) {
                    continue;
                }

                throw new UnsupportedReader(
                    message: sprintf(
                        'Unsupported configuration file found in aggregate reader: %s',
                        (string) $exception->key
                    ),
                    code: 0,
                    previous: $exception,
                );
            }

            $reader->fromFile($filepath);

            $values = array_merge_recursive(
                $values,
                $this->getConfigurationValuesForFile(
                    file: $file,
                    fileValues: $reader->read()
                ),
            );
        }

        return $values;
    }

    /**
     * @param SplFileInfo $file
     * @param array<string, mixed> $fileValues
     *
     * @return array<string, mixed>
     */
    private function getConfigurationValuesForFile(SplFileInfo $file, array $fileValues): array
    {
        return array_reduce(
            $this->getPrefixPartsForConfigurationFile($file),
            function (array $carry, string $part) {
                $carry = [ $part => $carry ];

                return $carry;
            },
            $fileValues,
        );
    }

    /**
     * @param SplFileInfo $file
     *
     * @return array<int, string>
     */
    private function getPrefixPartsForConfigurationFile(SplFileInfo $file): array
    {
        /**
         * $prefix can be one of these values:
         * - "" (empty string)
         * - "directory"
         * - "directory/child-directory"
         * - "directory/child-directory/grand-child-directory/..."
         *
         * The $prefix contains the parts found between the configuration file path and the
         * root directory. We need to nest the configuration values based on the directory
         * hierarchy. So instead of setting it up from top level to bottom level, we do the reverse
         * by building the nested array from the bottom up.
         */
        $prefix = trim(str_replace($this->directoryPath, '', $file->getPath()), '\/ ');
        $prefixParts = array_filter(explode(DIRECTORY_SEPARATOR, $prefix));

        $basename = pathinfo($file->getFilename(), PATHINFO_FILENAME);

        array_push($prefixParts, $basename);

        return array_reverse($prefixParts);
    }

    private function readerShouldBeIgnored(ReaderKey $key): bool
    {
        return $key->equals(...$this->readersToIgnore);
    }
}
