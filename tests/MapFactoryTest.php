<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Map;
use Grixu\Synchronizer\MapFactory;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class MapFactoryTest extends TestCase
{
    /** @test */
    public function making_map_based_on_dto()
    {
        $dto = ProductDataFactory::new()->create();
        $map = MapFactory::makeFromDto($dto,Product::class);

        $this->assertEquals(Map::class, get_class($map));
        $this->assertNotEmpty($map->get());
        $this->assertCount(count($dto->toArray()), $map->get());
    }

    /** @test */
    public function making_map_based_on_array()
    {
        $map = MapFactory::makeFromArray([
            'name' => 'name'
                                         ], Product::class);

        $this->assertEquals(Map::class, get_class($map));
        $this->assertNotEmpty($map->get());
        $this->assertCount(1, $map->get());
    }
}
