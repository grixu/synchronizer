<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Checksum;
use Grixu\Synchronizer\Transformer;
use Grixu\Synchronizer\Map;
use Grixu\Synchronizer\MapFactory;
use Grixu\Synchronizer\Tests\Helpers\MigrateProductsTrait;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class TransformerTest extends TestCase
{
    use MigrateProductsTrait;

    protected array $input;
    protected Map $map;
    protected Transformer $obj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateProducts();
        $this->input = ProductDataFactory::new()->create()->toArray();
        $this->map = MapFactory::makeFromArray($this->input, Product::class);
    }

    /** @test */
    public function it_create_array_to_sync()
    {
        Checksum::setChecksumField(null);
        $this->map = MapFactory::makeFromArray($this->input, Product::class);
        $obj = new Transformer($this->map);
        $returnedData = $obj->sync($this->input);

        $this->assertNotEmpty($returnedData);
        $this->assertCount(count($this->map->get()), $returnedData);
    }

    /** @test */
    public function it_create_array_to_sync_with_checksum()
    {
        Checksum::setChecksumField(config('synchronizer.checksum.field'));
        $this->input['checksum'] = 'aaaa';
        $obj = new Transformer($this->map);
        $returnedData = $obj->sync($this->input);

        $this->assertNotEmpty($returnedData);
        $this->assertCount(count($this->map->get()), $returnedData);
    }
}
