<?php

declare(strict_types=1);

namespace Themosis\Components\Config\Tests;

use PHPUnit\Framework\Attributes\Test;
use Themosis\Components\Config\Config;
use Themosis\Components\Config\Reader\AggregateReader;
use Themosis\Components\Config\Reader\PhpReader;
use Themosis\Components\Filesystem\LocalFilesystem;

final class ConfigAggregatorTest extends TestCase
{
    #[Test]
    public function it_can_read_configuration_from_aggregated_files_in_a_directory(): void
    {
        $reader = new AggregateReader( 
            filesystem: new LocalFilesystem,
            readers: [new PhpReader( new LocalFilesystem )],
        );
        $reader->from_directory(__DIR__ . '/Fixtures/config/*.php');

        $config = new Config(reader: $reader);

        $this->assertSame('Themosis', $config->get('app.name'));
    }
}
