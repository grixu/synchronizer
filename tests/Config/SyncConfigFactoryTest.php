<?php

namespace Grixu\Synchronizer\Tests\Config;

use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Config\SyncConfigFactory;
use Grixu\Synchronizer\Tests\Helpers\FakeErrorHandler;
use Grixu\Synchronizer\Tests\Helpers\FakeForeignSqlSourceModel;
use Grixu\Synchronizer\Tests\Helpers\FakeLoader;
use Grixu\Synchronizer\Tests\Helpers\FakeParser;
use Grixu\Synchronizer\Tests\Helpers\FakeSyncHandler;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Queue\SerializableClosure;

class SyncConfigFactoryTest extends TestCase
{
    protected SyncConfigFactory $obj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->obj = new SyncConfigFactory();
    }

    /** @test */
    public function it_returns_sync_config_object()
    {
        $config = $this->obj->make(
            loaderClass: FakeLoader::class,
            parserClass: FakeParser::class,
            localModel: FakeForeignSqlSourceModel::class,
            foreignKey: 'xlId',
            idsToSync: null,
            syncClosure: new SerializableClosure(function ($collection, $config) {}),
            errorHandler: new SerializableClosure(function ($e) {})
        );

        $this->basicAssertions($config);
    }

    protected function basicAssertions($config)
    {
        $this->assertEquals(SyncConfig::class, $config::class);
        $this->assertNotEmpty($config->getErrorHandler());
        $this->assertNotEmpty($config->getSyncClosure());
    }

    /**
     * @test
     * @environment-setup useHandlersConfig
     */
    public function it_returns_sync_config_object_with_default_handlers()
    {
        $config = $this->obj->make(
            loaderClass: FakeLoader::class,
            parserClass: FakeParser::class,
            localModel: FakeForeignSqlSourceModel::class,
            foreignKey: 'xlId',
            idsToSync: null,
            syncClosure: null,
            errorHandler: null
        );

        $this->basicAssertions($config);
    }

    protected function useHandlersConfig($app)
    {
        $app->config->set('synchronizer.handlers', [
            'sync' => FakeSyncHandler::class,
            'error' => FakeErrorHandler::class,
        ]);
    }

    /** @test */
    public function it_could_be_made_by_make_method()
    {
        $obj = $this->makeObj();

        $this->assertEquals(SyncConfig::class, $obj::class);
    }

    protected function makeObj(): SyncConfig
    {
         return $this->obj->make(
            loaderClass: FakeLoader::class,
            parserClass: FakeParser::class,
            localModel: FakeForeignSqlSourceModel::class,
            foreignKey: 'xlId',
            idsToSync: null,
            syncClosure: new SerializableClosure(function ($collection, $config) {}),
            errorHandler: new SerializableClosure(function ($e) {})
        );
    }

    /** @test */
    public function it_could_set_ids_to_sync()
    {
        $obj = $this->makeObj();
        $obj->setIdsToSync([1, 2, 3]);

        $this->assertNotEmpty($obj->getIdsToSync());
    }
}
