<?php

namespace Grixu\Synchronizer\Tests\Config;

use Exception;
use Grixu\SociusModels\Description\Models\Language;
use Grixu\Synchronizer\Config\Contracts\SyncConfig as SyncConfigInterface;
use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Config\Exceptions\InterfaceNotImplemented;
use Grixu\Synchronizer\Tests\Helpers\FakeLoader;
use Grixu\Synchronizer\Tests\Helpers\FakeParser;
use Grixu\Synchronizer\Tests\Helpers\FakeSyncConfig;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Queue\SerializableClosure;
use Illuminate\Support\Collection;
use Throwable;

class SyncConfigTest extends TestCase
{
    /** @test */
    public function it_could_take_every_argument()
    {
        $obj = $this->createObj();

        $this->assertEquals(SyncConfig::class, $obj::class);
    }

    protected function createObj(): SyncConfig
    {
        return new SyncConfig(
            loaderClass: FakeLoader::class,
            parserClass: FakeParser::class,
            localModel: Language::class,
            foreignKey: 'xlId',
            jobsConfig: config('synchronizer.jobs.default'),
            checksumField: null,
            timestamps: [],
            syncClosure: new SerializableClosure(
                function (Collection $dtoCollection, SyncConfig $config) {
                }
            ),
            errorHandler: new SerializableClosure(
                function (Throwable $e) {
                }
            )
        );
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
                jobsConfig: config('synchronizer.jobs.default'),
                checksumField: null,
                timestamps: [],
                syncClosure: new SerializableClosure(
                    function (Collection $dtoCollection, SyncConfig $config) {
                    }
                ),
                errorHandler: new SerializableClosure(
                    function (Throwable $e) {
                    }
                )
            );

            $this->assertTrue(false);
        } catch (InterfaceNotImplemented) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_throws_exception_when_current_job_is_lower_than_zero()
    {
        $config = FakeSyncConfig::make();

        try {
            $config->setCurrentJob(-1);
            $this->assertTrue(false);
        } catch (Exception) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_throws_exception_when_current_job_is_greater_than_jobs_count()
    {
        $config = FakeSyncConfig::make();

        try {
            $config->setCurrentJob(count(config('synchronizer.jobs.default')) + 1);
            $this->assertTrue(false);
        } catch (Exception) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_convert_closure_to_serializable_closure_on_sync_handler_setter()
    {
        $config = $this->createObj();

        $config->setSyncClosure(function () {
        });

        $this->assertNotEmpty($config->getSyncClosure());
        $this->assertEquals(SerializableClosure::class, $config->getSyncClosure()::class);
    }

    /** @test */
    public function it_convert_closure_to_serializable_closure_on_error_handler_setter()
    {
        $config = $this->createObj();

        $config->setErrorHandler(function () {
        });

        $this->assertNotEmpty($config->getErrorHandler());
        $this->assertEquals(SerializableClosure::class, $config->getErrorHandler()::class);
    }

    /** @test */
    public function it_provide_access_to_checksum_field_name()
    {
        $obj = $this->makeObjWithChecksum();
        $returnedValue = $obj->getChecksumField();

        $this->assertEquals('checksum', $returnedValue);
    }

    protected function makeObjWithChecksum($timestamps = []): SyncConfig
    {
        return new SyncConfig(
            loaderClass: FakeLoader::class,
            parserClass: FakeParser::class,
            localModel: Language::class,
            foreignKey: 'xlId',
            jobsConfig: config('synchronizer.jobs.default'),
            checksumField: 'checksum',
            timestamps: $timestamps,
            syncClosure: new SerializableClosure(
                function (Collection $dtoCollection, SyncConfig $config) {
                }
            ),
            errorHandler: new SerializableClosure(
                function (Throwable $e) {
                }
            )
        );
    }

    /**
     * @test
     * @environment-setup useDisabledChecksum
     */
    public function it_not_allows_checksum_field_if_checking_system_is_off()
    {
        $obj = $this->makeObjWithChecksum();
        $this->assertEmpty($obj->getChecksumField());
    }

    protected function useDisabledChecksum($app)
    {
        $app->config->set('synchronizer.checksum.control', false);
    }

    /** @test */
    public function it_provide_access_to_timestamp_fields()
    {
        $timestamps = ['one'];
        $obj = $this->makeObjWithChecksum($timestamps);

        $this->assertNotEmpty($obj->getTimestamps());
        $this->assertEquals($timestamps, $obj->getTimestamps());
    }

    /**
     * @test
     * @environment-setup useDisabledChecksum
     */
    public function it_block_access_to_timestamps_when_checksum_checking_is_disabled()
    {
        $timestamps = ['one'];
        $obj = $this->makeObjWithChecksum($timestamps);

        $this->assertEmpty($obj->getTimestamps());
    }

    /** @test */
    public function it_provide_access_to_ids_array()
    {
        $ids = [1,2,3];
        $obj = $this->makeObjWithIds($ids);
        $returnedValue = $obj->getIds();

        $this->assertEquals($ids, $returnedValue);
    }

    protected function makeObjWithIds($ids = []): SyncConfig
    {
        return new SyncConfig(
            loaderClass: FakeLoader::class,
            parserClass: FakeParser::class,
            localModel: Language::class,
            foreignKey: 'xlId',
            jobsConfig: config('synchronizer.jobs.default'),
            checksumField: 'checksum',
            timestamps: [],
            ids: $ids,
            syncClosure: new SerializableClosure(
                function (Collection $dtoCollection, SyncConfig $config) {
                }
            ),
            errorHandler: new SerializableClosure(
                function (Throwable $e) {
                }
            )
        );
    }

    /** @test */
    public function it_is_singleton()
    {
        $obj = SyncConfig::getInstance();

        $this->assertTrue($obj instanceof SyncConfigInterface);
    }

    /** @test */
    public function it_is_singleton_and_could_set_instance()
    {
        $instanceBefore = SyncConfig::getInstance();
        $obj = $this->makeObjWithIds();
        SyncConfig::setInstance($obj);

        $this->assertNotEquals($instanceBefore, SyncConfig::getInstance());
        $this->assertEquals($obj, app(SyncConfigInterface::class));
    }
}
