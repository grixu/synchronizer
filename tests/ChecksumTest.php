<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Checksum;
use Grixu\Synchronizer\Exceptions\EmptyMd5FieldNameInConfigException;
use Grixu\Synchronizer\Map;
use Grixu\Synchronizer\Tests\Helpers\MigrateProductsTrait;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Support\Facades\Hash;

class ChecksumTest extends TestCase
{
    use MigrateProductsTrait;

    protected Checksum $obj;
    protected Product $model;
    protected Map $map;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateProducts();
        $this->model = Product::factory()->create(
            [
                'brand_id' => null,
                'product_type_id' => null
            ]
        );

        $this->map = new Map(
            [
                'name' => 'name',
                'updatedAt' => 'updated_at'
            ], Product::class
        );

        $this->obj = new Checksum($this->map, $this->model);
    }

    protected function checksumControlOff($app)
    {
        $app['config']->set('synchronizer.checksum.control', false);
    }

    protected function checksumFieldEmpty($app)
    {
        $app['config']->set('synchronizer.checksum.field', null);
    }

    /** @test */
    public function creates_obj()
    {
        $this->assertEquals(Checksum::class, get_class($this->obj));
    }

    /** @test */
    public function it_calculates_checksum()
    {
        $this->assertNotEmpty($this->obj->getChecksum());
        $this->assertIsString($this->obj->getChecksum());
    }

    /** @test */
    public function it_validating_checksum()
    {
        $this->assertNotEmpty(config('synchronizer.checksum.field'));
        $this->assertTrue(config('synchronizer.checksum.control'));
        $this->assertIsBool($this->obj->validate());
        $this->assertFalse($this->obj->validate());
    }

    /** @test */
    public function it_updating_checksum()
    {
        $this->obj->update();

        $this->model->refresh();
        $this->assertNotEmpty($this->model->checksum);
    }

    /** @test */
    public function it_validates_checksum_properly()
    {
        $this->model->checksum = $this->obj->getChecksum();
        $this->obj = new Checksum($this->map, $this->model);

        $this->assertTrue($this->obj->validate());
    }

    /** @test */
    public function hash_can_be_used()
    {
        $preGenerated =
            json_encode(
                $this->model->only(
                    $this->map->getModelFieldsArray()
                )
            );

        $this->assertTrue(Hash::check($preGenerated, $this->obj->getChecksum()));
    }

    /**
     * @test
     * @environment-setup checksumControlOff
     */
    public function it_not_updating_model_when_checksum_is_off()
    {
        $this->obj->update();

        $this->model->refresh();
        $this->assertEmpty($this->model->checksum);
    }

    /**
     * @test
     * @environment-setup checksumControlOff
     */
    public function it_falsy_validation_when_checksum_is_off()
    {
        $this->assertIsBool($this->obj->validate());
        $this->assertFalse($this->obj->validate());
    }

    /**
     * @test
     * @environment-setup checksumFieldEmpty
     */
    public function it_throw_exception_on_validation_when_checksum_is_on_but_checksum_field_is_null()
    {
        try {
            $this->obj->validate();
            $this->assertTrue(false);
        } catch (EmptyMd5FieldNameInConfigException) {
            $this->assertTrue(true);
        }
    }

    /**
     * @test
     * @environment-setup checksumFieldEmpty
     */
    public function it_throw_exception_on_update_when_checksum_is_on_but_checksum_field_is_null()
    {
        try {
            $this->obj->update();
            $this->assertTrue(false);
        } catch (EmptyMd5FieldNameInConfigException) {
            $this->assertTrue(true);
        }
    }
}
