<?php

namespace Grixu\Synchronizer\Tests\Engine\Map;

use Grixu\SociusModels\Customer\Models\Customer;
use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Engine\Map\Map;
use Grixu\Synchronizer\Engine\Models\ExcludedField;
use Grixu\Synchronizer\Tests\Helpers\FakeSyncConfig;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class MapTest extends TestCase
{
    protected Map $obj;
    protected SyncConfig $config;

    protected function setUp(): void
    {
        parent::setUp();
        SyncConfig::setInstance(FakeSyncConfig::make('checksum', ['updated_at']));
    }

    /** @test */
    public function create_map(): void
    {
        $this->createObj();
        $this->assertEquals(Map::class, get_class($this->obj));
    }

    protected function createObj(): void
    {
        $this->obj = new Map(
            [
                'name',
                'country',
                'updated_at'
            ]
        );
    }

    /** @test */
    public function get_filtered_collection(): void
    {
        $this->createObj();

        $this->assertNotEmpty($this->obj->get());
        $this->assertCount(4, $this->obj->get());
    }

    /** @test */
    public function get_filtered_with_excluded_fields()
    {
        $this->createSimpleExclude();
        $this->createObj();

        $this->assertNotEmpty($this->obj->get());
        $this->assertCount(3, $this->obj->get());
    }

    protected function createSimpleExclude(): void
    {
        ExcludedField::factory()->create(
            [
                'field' => 'country',
                'model' => Customer::class,
                'update_empty' => false
            ]
        );
    }

    /** @test */
    public function get_filtered_with_excluded_update_on_null_fields()
    {
        $this->createObjAndRealModel();
        $this->assertNotEmpty($this->obj->get());
        $this->assertCount(2, $this->obj->get());
        $this->assertCount(1, $this->obj->getWithoutTimestamps());
        $this->assertCount(1, $this->obj->getUpdatableOnNullFields());
    }

    protected function createObjAndRealModel(): Customer
    {
        ExcludedField::factory()->create(
            [
                'field' => 'name',
                'model' => Customer::class,
                'update_empty' => true,
            ]
        );
        $model = Customer::factory()->make();

        $this->obj = new Map(
            [
                'name',
                'updated_at'
            ],
        );

        return $model;
    }

    /**
     * @test
     * @environment-setup timestampConfig
     */
    public function get_collection_without_timestamps()
    {
        $this->createObj();

        $this->assertNotEmpty($this->obj->getWithoutTimestamps());
        $this->assertCount(3, $this->obj->getWithoutTimestamps());
    }

    /** @test */
    public function get_array_with_filtered_models_fields()
    {
        $this->createObj();

        $this->assertBasicThingsAboutArray();
        $this->assertCount(4, $this->obj->getModelFieldsArray());
    }

    protected function assertBasicThingsAboutArray()
    {
        $this->assertIsArray($this->obj->getModelFieldsArray());
        $this->assertNotEmpty($this->obj->getModelFieldsArray());
    }

    /** @test */
    public function get_array_with_filtered_models_fields_but_one_excluded()
    {
        $this->createSimpleExclude();
        $this->createObj();

        $this->assertBasicThingsAboutArray();
        $this->assertCount(3, $this->obj->getModelFieldsArray());
    }

    /** @test */
    public function get_array_with_filtered_models_fields_but_one_excluded_with_null_update_option()
    {
        $this->createObjAndRealModel();

        $this->assertBasicThingsAboutArray();
        $this->assertCount(2, $this->obj->getModelFieldsArray());
    }
}
