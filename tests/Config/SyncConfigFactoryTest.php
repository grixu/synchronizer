<?php

namespace Grixu\Synchronizer\Tests\Config;

use Exception;
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

    /** @test */
    public function it_could_take_config_string()
    {
        $config = $this->obj->make(
            loaderClass: FakeLoader::class,
            parserClass: FakeParser::class,
            localModel: FakeForeignSqlSourceModel::class,
            foreignKey: 'xlId',
            jobsConfig: 'default',
            idsToSync: null,
            syncClosure: new SerializableClosure(function ($collection, $config) {}),
            errorHandler: new SerializableClosure(function ($e) {})
        );

        $this->assertJobs($config);
    }

    protected function assertJobs(SyncConfig $config)
    {
        $this->assertNotEmpty($config->getCurrentJob());
        $this->assertNotEmpty($config->getNextJob());
    }


    /** @test */
    public function it_set_default_job_config()
    {
        $config = $this->makeObj();
        $this->assertJobs($config);
    }

    /**
     * @test
     * @environment-setup useEmptyJobConfig
     */
    public function it_throws_exception_if_there_is_no_default_job_config()
    {
        try {
            $this->makeObj();
            $this->assertTrue(false);
        } catch (Exception) {
            $this->assertTrue(true);
        }
    }

    protected function useEmptyJobConfig($app)
    {
        $app->config->set('synchronizer.jobs', [
            'default' => [],
        ]);
    }

    /** @test */
    public function it_returns_object_when_checksum_defined()
    {
        $config = $this->makeObjWithChecksum();

        $this->basicAssertions($config);
        $this->assertNotEmpty($config->getChecksumField());
    }

    protected function makeObjWithChecksum(): SyncConfig
    {
        return $this->obj->make(
            loaderClass: FakeLoader::class,
            parserClass: FakeParser::class,
            localModel: FakeForeignSqlSourceModel::class,
            foreignKey: 'xlId',
            checksumField: 'checksum',
            idsToSync: null,
            syncClosure: new SerializableClosure(function ($collection, $config) {}),
            errorHandler: new SerializableClosure(function ($e) {})
        );
    }

    /**
     * @test
     * @environment-setup useDisabledChecksum
     */
    public function it_allows_to_empty_checksum()
    {
        $config = $this->makeObj();

        $this->basicAssertions($config);
        $this->assertEmpty($config->getChecksumField());
    }

    protected function useDisabledChecksum($app)
    {
        $app->config->set('synchronizer.checksum.control', false);
    }
}
