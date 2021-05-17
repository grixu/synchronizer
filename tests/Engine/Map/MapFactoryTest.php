<?php

namespace Grixu\Synchronizer\Tests\Engine\Map;

use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Engine\Map\Map;
use Grixu\Synchronizer\Engine\Map\MapFactory;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class MapFactoryTest extends TestCase
{
    /** @test */
    public function making_map_based_on_dto()
    {
        $dto = ProductDataFactory::new()->create();
        $map = MapFactory::makeFromDto($dto, Product::class);

        $this->assertEquals(Map::class, get_class($map));
        $this->assertNotEmpty($map->get());
        $this->assertCount(count($dto->except('relationships')->toArray()), $map->get());
    }

    /** @test */
    public function making_map_from_array()
    {
        $map = MapFactory::make(
            [
                'name' => 'name'
            ],
            Product::class
        );

        $this->assertEquals(Map::class, get_class($map));
        $this->assertNotEmpty($map->get());
        $this->assertCount(2, $map->get());
    }

    /** @test */
    public function making_map_based_on_array()
    {
        $dto = ProductDataFactory::new()->create()->toArray();
        $map = MapFactory::makeFromArray($dto, Product::class);

        $this->assertEquals(Map::class, get_class($map));
        $this->assertNotEmpty($map->get());
        $this->assertCount(count($dto), $map->get());
    }
}
