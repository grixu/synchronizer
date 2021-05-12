<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Map;
use Grixu\Synchronizer\MapFactory;
use Grixu\Synchronizer\ModelSynchronizer;
use Grixu\Synchronizer\Tests\Helpers\MigrateProductsTrait;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class ModelSynchronizerTest extends TestCase
{
    use MigrateProductsTrait;

    protected array $input;
    protected Map $map;
    protected ModelSynchronizer $obj;

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
        $obj = new ModelSynchronizer($this->map, null);
        $returnedData = $obj->sync($this->input);

        $this->assertNotEmpty($returnedData);
        $this->assertCount(count($this->map->get()), $returnedData);
    }

    /** @test */
    public function it_create_array_to_sync_with_checksum()
    {
        $this->input['checksum'] = 'aaaa';
        $obj = new ModelSynchronizer($this->map, config('synchronizer.checksum.field'));
        $returnedData = $obj->sync($this->input);

        $this->assertNotEmpty($returnedData);
        $this->assertCount(count($this->map->get()) + 1, $returnedData);
    }
}
