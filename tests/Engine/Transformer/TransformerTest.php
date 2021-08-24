<?php

namespace Grixu\Synchronizer\Tests\Engine\Transformer;

use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Engine\Map\Map;
use Grixu\Synchronizer\Engine\Map\MapFactory;
use Grixu\Synchronizer\Engine\Transformer\Transformer;
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
    }

    /** @test */
    public function it_create_array_to_sync()
    {
        $this->map = MapFactory::makeFromArray($this->input, Product::class, null);
        $obj = new Transformer($this->map);
        $returnedData = $obj->sync($this->input);

        $this->assertNotEmpty($returnedData);
        $this->assertCount(count($this->map->get()), $returnedData);
    }

    /** @test */
    public function it_create_array_to_sync_with_checksum()
    {
        $this->map = MapFactory::makeFromArray($this->input, Product::class, 'aaaa');
        $obj = new Transformer($this->map);
        $returnedData = $obj->sync($this->input);

        $this->assertNotEmpty($returnedData);
        $this->assertCount(count($this->map->get()), $returnedData);
    }
}
