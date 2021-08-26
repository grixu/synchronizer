<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\Config\ProcessConfig;

class FakeProcessConfig
{
    public static function make(array $config = []): ProcessConfig
    {
        if (empty($config)) {
            $config = config('synchronizer.jobs.default');
        }

        return ProcessConfig::make(
            FakeLoader::class,
            FakeParser::class,
            $config,
        );
    }

    public static function makeLoadAllAndParse(): ProcessConfig
    {
        return self::make(config('synchronizer.jobs.load-all-and-parse'));
    }

    public static function makeChunkLoadAndParse(): ProcessConfig
    {
        return self::make(config('synchronizer.jobs.chunk-load-and-parse'));
    }

    public static function makeArray(): array
    {
        return [
            FakeLoader::class,
            FakeParser::class,
            config('synchronizer.jobs.default'),
            config('synchronizer.handlers.sync'),
            config('synchronizer.handlers.error'),
        ];
    }
}
