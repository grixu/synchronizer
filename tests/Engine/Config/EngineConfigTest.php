<?php

namespace Grixu\Synchronizer\Tests\Engine\Config;

use Grixu\SociusModels\Description\Models\Language;
use Grixu\Synchronizer\Engine\Config\EngineConfig;
use Grixu\Synchronizer\Engine\Config\EngineConfigFactory;
use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class EngineConfigTest extends TestCase
{
    /** @test */
    public function it_could_take_every_argument()
    {
        $obj = $this->createObj();

        $this->assertInstanceOf(EngineConfigInterface::class, $obj);
    }

    protected function createObj(
        $timestamps = [],
        $fields = [],
        $mode = EngineConfig::EXCLUDED,
        $checksumField = 'checksum',
        $ids = []
    ): EngineConfigInterface {
        return EngineConfigFactory::make(
            model: Language::class,
            key: 'xlId',
            fields: $fields,
            mode: $mode,
            checksumField: $checksumField,
            timestamps: $timestamps,
            ids: $ids
        );
    }

    /** @test */
    public function it_provide_access_to_checksum_field_name()
    {
        $obj = $this->createObj();
        $returnedValue = $obj->getChecksumField();

        $this->assertEquals('checksum', $returnedValue);
    }

    /**
     * @test
     * @environment-setup useDisabledChecksum
     */
    public function it_not_allows_checksum_field_if_checking_system_is_off()
    {
        $obj = $this->createObj();
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
        $obj = $this->createObj(timestamps: $timestamps);

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
        $obj = $this->createObj(timestamps: $timestamps);

        $this->assertEmpty($obj->getTimestamps());
    }

    /** @test */
    public function it_provide_access_to_ids_array()
    {
        $ids = [1,2,3];
        $obj = $this->createObj(ids: $ids);
        $returnedValue = $obj->getIds();

        $this->assertEquals($ids, $returnedValue);
    }

    /** @test */
    public function it_is_singleton()
    {
        $obj = EngineConfig::getInstance();

        $this->assertTrue($obj instanceof EngineConfigInterface);
    }

    /** @test */
    public function it_is_singleton_and_could_set_instance()
    {
        $instanceBefore = EngineConfig::getInstance();
        $obj = $this->createObj();
        EngineConfig::setInstance($obj);

        $this->assertNotEquals($instanceBefore, EngineConfig::getInstance());
        $this->assertEquals($obj, app(EngineConfigInterface::class));
    }

    /** @test */
    public function it_provide_excluded_fields_mode()
    {
        $excluded = ['name'];
        $obj = $this->createObj(fields: $excluded);

        $this->assertNotEmpty($obj->getExcluded());
        $this->assertEmpty($obj->getFillable());
        $this->assertEmpty($obj->getOnly());
        $this->assertFalse($obj->isOnlyMode());
    }

    /** @test */
    public function it_provide_fillable_fields_mode()
    {
        $fillable = ['name' => ['fillable']];
        $obj = $this->createObj(fields: $fillable);

        $this->assertEmpty($obj->getExcluded());
        $this->assertNotEmpty($obj->getFillable());
        $this->assertEmpty($obj->getOnly());
        $this->assertFalse($obj->isOnlyMode());
    }

    /** @test */
    public function it_provide_only_fields_mode()
    {
        $only = ['name'];
        $obj = $this->createObj(fields: $only, mode: EngineConfig::ONLY);

        $this->assertEmpty($obj->getExcluded());
        $this->assertEmpty($obj->getFillable());
        $this->assertNotEmpty($obj->getOnly());
        $this->assertTrue($obj->isOnlyMode());
    }

    /** @test */
    public function it_is_bulletproof_for_wrong_field_config()
    {
        $excluded = ['name'=>[]];
        $obj = $this->createObj(fields: $excluded);

        $this->assertNotEmpty($obj->getExcluded());
        $this->assertEmpty($obj->getFillable());
        $this->assertEmpty($obj->getOnly());
        $this->assertFalse($obj->isOnlyMode());
    }

    /** @test */
    public function it_secure_key_by_auto_adding_in_only_mode()
    {
        $only = ['name'];
        $obj = $this->createObj(fields: $only, mode: EngineConfig::ONLY);

        $this->assertEmpty($obj->getExcluded());
        $this->assertEmpty($obj->getFillable());
        $this->assertNotEmpty($obj->getOnly());
        $this->assertCount(2, $obj->getOnly());
        $this->assertTrue($obj->isOnlyMode());
    }

    /** @test */
    public function it_secure_key_by_auto_removing_in_excluding_mode()
    {
        $excluded = ['name', 'xlId'];
        $obj = $this->createObj(fields: $excluded);

        $this->assertNotEmpty($obj->getExcluded());
        $this->assertCount(1, $obj->getExcluded());
        $this->assertEmpty($obj->getFillable());
        $this->assertEmpty($obj->getOnly());
        $this->assertFalse($obj->isOnlyMode());
    }
}
