<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\SociusModels\Product\Factories\ProductDataFactory;
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
    protected Map $map;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateProducts();
    }

    /** @test */
    public function it_pass_all_not_created_obj()
    {
        $data = ProductDataFactory::new()->times(10)->create();
        foreach ($data as $row) {
            Product::factory()->create(['xl_id' => $row->xlId]);
            $row->checksum = hash('crc32c', json_encode($row));
        }

        $obj = new Checksum($data, 'xlId', Product::class);

        $this->assertNotEmpty($obj->get());
        $this->assertCount(10, $obj->get());
    }

    /** @test */
    public function it_not_passing_already_created_objs_with_same_checksum()
    {
        $data = ProductDataFactory::new()->times(10)->create();
        foreach ($data as $row) {
            $row->checksum = hash('crc32c', json_encode($row));
            Product::factory()->create(['xl_id' => $row->xlId, 'checksum' => $row->checksum]);
        }

        $obj = new Checksum($data, 'xlId', Product::class);

        $this->assertEmpty($obj->get());
    }

    /** @test */
    public function it_passing_already_created_obj_with_another_checksum()
    {
        $data = ProductDataFactory::new()->times(10)->create();
        foreach ($data as $row) {
            $row->checksum = hash('crc32c', json_encode($row));
            Product::factory()->create(['xl_id' => $row->xlId, 'checksum' => $row->checksum . '_a']);
        }

        $obj = new Checksum($data, 'xlId', Product::class);

        $this->assertNotEmpty($obj->get());
        $this->assertCount(10, $obj->get());
    }

    /** @test */
    public function it_generate_checksum()
    {
        $data = [
            'test' => 'Testing'
        ];
        $checksumGenerated = Checksum::generate($data);

        $this->assertNotEmpty($checksumGenerated);
        $this->assertEquals(hash('crc32c', json_encode($data)), $checksumGenerated);
    }
}
