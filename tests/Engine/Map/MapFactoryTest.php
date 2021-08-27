<?php

namespace Grixu\Synchronizer\Tests\Engine\Map;

use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\Synchronizer\Config\EngineConfig;
use Grixu\Synchronizer\Engine\Map\Map;
use Grixu\Synchronizer\Engine\Map\MapFactory;
use Grixu\Synchronizer\Tests\Helpers\FakeEngineConfig;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class MapFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        EngineConfig::setInstance(FakeEngineConfig::make());
    }

    /** @test */
    public function making_map_based_on_dto()
    {
        $dto = ProductDataFactory::new()->create();
        $map = MapFactory::makeFromDto($dto);

        $this->assertEquals(Map::class, get_class($map));
        $this->assertNotEmpty($map->get());
        $this->assertCount(count($dto->except('relationships')->toArray()), $map->get());
    }

    /** @test */
    public function making_map_from_array()
    {
        $map = MapFactory::make(
            [
                'name' => 'name',
            ]
        );

        $this->assertEquals(Map::class, get_class($map));
        $this->assertNotEmpty($map->get());
        $this->assertCount(2, $map->get());
    }

    /** @test */
    public function making_map_based_on_array()
    {
        $dto = ProductDataFactory::new()->create()->toArray();
        $map = MapFactory::makeFromArray($dto);

        $this->assertEquals(Map::class, get_class($map));
        $this->assertNotEmpty($map->get());
        $this->assertCount(count($dto), $map->get());
    }
}
