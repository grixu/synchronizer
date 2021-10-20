<?php

namespace Grixu\Synchronizer\Tests\Engine\Map;

use Grixu\SociusModels\Customer\Models\Customer;
use Grixu\Synchronizer\Engine\Config\EngineConfig;
use Grixu\Synchronizer\Engine\Map\Map;
use Grixu\Synchronizer\Tests\Helpers\FakeEngineConfig;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class MapTest extends TestCase
{
    protected Map $obj;
    protected EngineConfig $config;

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
                'updatedAt',
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
        EngineConfig::setInstance(
            FakeEngineConfig::make(
                timestamps: ['updated_at'],
                checksumField: 'checksum',
                fields: ['country']
            )
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
        EngineConfig::setInstance(
            FakeEngineConfig::make(
                timestamps: ['updated_at'],
                checksumField: 'checksum',
                fields: ['fillable' => ['name']]
            )
        );
        $model = Customer::factory()->make();

        $this->obj = new Map(
            [
                'name',
                'updatedAt',
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

    /** @test */
    public function it_transform_updatable_fields_to_map()
    {
        EngineConfig::setInstance(
            FakeEngineConfig::make(
                timestamps: ['updated_at'],
                checksumField: 'checksum',
                fields: ['fillable' => ['postal_code']]
            )
        );
        $model = Customer::factory()->make();

        $this->obj = new Map(
            [
                'name',
                'postalCode',
                'updatedAt',
            ],
        );

        $this->assertCount(1, $this->obj->getUpdatableOnNullFields());
        $this->assertEquals(['postalCode'=>'postal_code'], $this->obj->getUpdatableOnNullFields());
    }

    protected function setUp(): void
    {
        parent::setUp();
        EngineConfig::setInstance(
            FakeEngineConfig::make(
                timestamps: ['updated_at'],
                checksumField: 'checksum'
            )
        );
    }
}
