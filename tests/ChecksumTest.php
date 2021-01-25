<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Checksum;
use Grixu\Synchronizer\Map;
use Grixu\Synchronizer\Tests\Helpers\MigrateProductsTrait;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class ChecksumTest extends TestCase
{
    use MigrateProductsTrait;

    protected Checksum $obj;
    protected Product $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateProducts();
        $this->model = Product::factory()->create(
            [
                'brandId' => null,
                'productTypeId' => null
            ]
        );

        $map = new Map(
            [
                'name' => 'name',
                'updatedAt' => 'updatedAt'
            ], Product::class
        );

        $this->obj = new Checksum($map, $this->model);
    }

    /** @test */
    public function creates_obj()
    {
        $this->assertEquals(Checksum::class, get_class($this->obj));
    }

    /** @test */
    public function it_calculates_md5()
    {
        $this->assertNotEmpty($this->obj->getMd5());
        $this->assertIsString($this->obj->getMd5());
    }

    /** @test */
    public function it_validating_md5()
    {
        $this->assertNotEmpty(config('synchronizer.checksum_field'));
        $this->assertTrue(config('synchronizer.checksum_control'));
        $this->assertIsBool($this->obj->validate());
        $this->assertFalse($this->obj->validate());
    }

    /** @test */
    public function it_updating_md5()
    {
        $this->obj->update();

        $this->model->refresh();
        $this->assertNotEmpty($this->model->checksum);
    }
}
