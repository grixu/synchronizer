<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Map;
use Grixu\Synchronizer\Models\ExcludedField;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Model;

class MapTest extends TestCase
{
    protected Map $obj;

    protected function setUp(): void
    {
        parent::setUp();
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
                'name' => 'name',
                'spam' => 'spam',
                'updatedAt' => 'updated_at'
            ],
            Model::class
        );
    }

    /** @test */
    public function get_filtered_collection(): void
    {
        $this->createObj();

        $this->assertNotEmpty($this->obj->get());
        $this->assertCount(3, $this->obj->get());
    }

    /** @test */
    public function get_filtered_with_excluded_fields()
    {
        $this->createSimpleExclude();
        $this->createObj();

        $this->assertNotEmpty($this->obj->get());
        $this->assertCount(2, $this->obj->get());
    }

    protected function createSimpleExclude(): void
    {
        ExcludedField::factory()->create(
            [
                'field' => 'spam',
                'model' => Model::class,
                'update_empty' => false
            ]
        );
    }

    /** @test */
    public function get_filtered_with_excluded_update_on_null_fields()
    {
        $model = $this->createObjAndRealModel();
        $this->assertNotEmpty($this->obj->get($model));
        $this->assertCount(1, $this->obj->get($model));
        $this->assertCount(0, $this->obj->getWithoutTimestamps($model));
    }

    protected function createObjAndRealModel(): Model
    {
        ExcludedField::factory()->create(
            [
                'field' => 'name',
                'model' => Product::class,
                'update_empty' => true,
            ]
        );
        $model = Product::factory()->make(
            [
                'brand_id' => null,
                'product_type_id' => null,
            ]
        );

        $this->obj = new Map(
            [
                'name' => 'name',
                'updatedAt' => 'updated_at'
            ],
            Product::class
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
        $this->assertCount(2, $this->obj->getWithoutTimestamps());
    }

    /** @test */
    public function get_array_with_filtered_models_fields()
    {
        $this->createObj();

        $this->assertBasicThingsAboutArray();
        $this->assertCount(3, $this->obj->getModelFieldsArray());
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
        $this->assertCount(2, $this->obj->getModelFieldsArray());
    }

    /** @test */
    public function get_array_with_filtered_models_fields_but_one_excluded_with_null_update_option()
    {
        $model = $this->createObjAndRealModel();

        $this->assertBasicThingsAboutArray();
        $this->assertCount(1, $this->obj->getModelFieldsArray($model));
    }

    /**
     * @test
     * @environment-setup useChecksumTimestampExcluded
     */
    public function get_array_with_models_fields_when_timestamps_exclude_is_on()
    {
        $this->createObj();

        $this->assertBasicThingsAboutArray();
        $this->assertCount(2, $this->obj->getModelFieldsArray());
    }

    protected function useChecksumTimestampExcluded($app)
    {
        $app->config->set('synchronizer.checksum.timestamps_excluded', true);
    }
}
