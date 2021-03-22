<?php

namespace Grixu\Synchronizer\Tests\Config;

use Grixu\SociusModels\Description\Models\Language;
use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Exceptions\InterfaceNotImplemented;
use Grixu\Synchronizer\Tests\Helpers\FakeLoader;
use Grixu\Synchronizer\Tests\Helpers\FakeParser;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;
use Throwable;

class SyncConfigTest extends TestCase
{
    /** @test */
    public function it_could_be_made_by_make_method()
    {
        $obj = $this->makeObj();

        $this->assertEquals(SyncConfig::class, $obj::class);
    }

    #[Pure]
    protected function makeObj(): SyncConfig
    {
        return SyncConfig::make(
            FakeLoader::class,
            FakeParser::class,
            Language::class,
            'xlId'
        );
    }

    /** @test */
    public function it_could_set_ids_to_sync()
    {
        $obj = $this->makeObj();
        $obj->setIdsToSync([1, 2, 3]);

        $this->assertNotEmpty($obj->getIdsToSync());
    }

    /** @test */
    public function it_could_take_every_argument()
    {
        $obj = new SyncConfig(
            loaderClass: FakeLoader::class,
            parserClass: FakeParser::class,
            localModel: Language::class,
            foreignKey: 'xlId',
            idsToSync: null,
            syncClosure: function (Collection $dtoCollection, SyncConfig $config) {
        },
            errorHandler: function (Throwable $e) {
        }
        );

        $this->assertEquals(SyncConfig::class, $obj::class);
    }

    /** @test */
    public function it_checking_interfaces_implemented_by_loader_and_parser()
    {
        try {
            new SyncConfig(
                loaderClass: Collection::class,
                parserClass: FakeParser::class,
                localModel: Language::class,
                foreignKey: 'xlId',
                idsToSync: null,
                syncClosure: function (Collection $dtoCollection, SyncConfig $config) {
            },
                errorHandler: function (Throwable $e) {
            }
            );

            $this->assertTrue(false);
        } catch (InterfaceNotImplemented) {
            $this->assertTrue(true);
        }
    }
}
