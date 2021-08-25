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
            timestamps: [],
            ids: [],
            syncClosure: new SerializableClosure(function ($collection, $config) {
            }),
            errorHandler: new SerializableClosure(function ($e) {
            })
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
            timestamps: [],
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
            timestamps: [],
            syncClosure: new SerializableClosure(function ($collection, $config) {
            }),
            errorHandler: new SerializableClosure(function ($e) {
            })
        );
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
            timestamps: [],
            syncClosure: new SerializableClosure(function ($collection, $config) {
            }),
            errorHandler: new SerializableClosure(function ($e) {
            })
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

    protected function makeObjWithChecksum(string|bool $checksum = 'checksum'): SyncConfig
    {
        return $this->obj->make(
            loaderClass: FakeLoader::class,
            parserClass: FakeParser::class,
            localModel: FakeForeignSqlSourceModel::class,
            foreignKey: 'xlId',
            checksumField: $checksum,
            timestamps: [],
            syncClosure: new SerializableClosure(function ($collection, $config) {
            }),
            errorHandler: new SerializableClosure(function ($e) {
            })
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

    /** @test */
    public function it_allows_to_disable_checksum()
    {
        $config = $this->makeObjWithChecksum(false);

        $this->assertEmpty($config->getChecksumField());
    }

    /** @test */
    public function it_returns_object_when_timestamps_defined()
    {
        $timestamps = ['one'];
        $config = $this->makeObjWithTimestamps($timestamps);

        $this->assertNotEmpty($config->getTimestamps());
        $this->assertEquals($timestamps, $config->getTimestamps());
    }

    protected function makeObjWithTimestamps(array|bool $timestamps = []): SyncConfig
    {
        return $this->obj->make(
            loaderClass: FakeLoader::class,
            parserClass: FakeParser::class,
            localModel: FakeForeignSqlSourceModel::class,
            foreignKey: 'xlId',
            checksumField: 'checksum',
            timestamps: $timestamps,
            syncClosure: new SerializableClosure(function ($collection, $config) {
            }),
            errorHandler: new SerializableClosure(function ($e) {
            })
        );
    }

    /** @test */
    public function it_returns_object_when_timestamps_disabled()
    {
        $config = $this->makeObjWithTimestamps(false);

        $this->assertEmpty($config->getTimestamps());
    }

    /** @test */
    public function it_returns_object_when_default_timestamps_if_not_defined()
    {
        $config = $this->makeObjWithTimestamps();

        $this->assertNotEmpty($config->getTimestamps());
        $this->assertEquals(config('synchronizer.checksum.timestamps'), $config->getTimestamps());
    }
}
