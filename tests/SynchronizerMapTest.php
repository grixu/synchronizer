<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\Synchronizer\SynchronizerMap;
use Grixu\Synchronizer\Tests\Helpers\BaseTestCase;
use Grixu\Synchronizer\Tests\Helpers\SynchronizerFieldFactory;
use Illuminate\Support\Collection;

/**
 * Class SynchronizerMapTest
 * @package Grixu\Synchronizer\Tests
 */
class SynchronizerMapTest extends BaseTestCase
{
    protected array $map;

    protected function setUp(): void
    {
        parent::setUp();

        $this->map = [
            'name' => 'name',
            'doubledName' => 'doubled_name',
            'age' => 'aged',
        ];
    }

    /** @test */
    public function check_constructor_with_no_fields_excluded()
    {
        $obj = new SynchronizerMap($this->map, 'Product');

        $this->assertEquals(Collection::class, get_class($obj->getMap()));
        $this->assertEquals(Collection::class, get_class($obj->getExcluded()));
        $this->assertEquals(Collection::class, get_class($obj->getToSync()));
        $this->assertEquals(Collection::class, get_class($obj->getExcludedNullUpdate()));
        $this->assertNotEmpty($obj->getMap());
        $this->assertNotEmpty($obj->getToSync());
        $this->assertEmpty($obj->getExcluded());
        $this->assertEmpty($obj->getExcludedNullUpdate());
    }

    /** @test */
    public function check_constructor_with_excluded_fields()
    {
        SynchronizerFieldFactory::new()->create(
            [
                'model' => 'Product',
                'field' => 'name',
                'update_empty' => 1,
            ]
        );

        $obj = new SynchronizerMap($this->map, 'Product');

        $this->assertEquals(Collection::class, get_class($obj->getMap()));
        $this->assertEquals(Collection::class, get_class($obj->getExcluded()));
        $this->assertEquals(Collection::class, get_class($obj->getToSync()));
        $this->assertNotEmpty($obj->getMap());
        $this->assertNotEmpty($obj->getToSync());
        $this->assertNotEmpty($obj->getExcluded());
        $this->assertCount(count($this->map), $obj->getMap());
        $this->assertCount($obj->getMap()->count()-1, $obj->getToSync());
        $this->assertCount(1, $obj->getExcluded());
        $this->assertCount(1, $obj->getExcludedNullUpdate());
    }

    /** @test */
    public function check_constructor_with_non_existing_excluded_fields()
    {
        SynchronizerFieldFactory::new()->create(
            [
                'model' => 'Product',
            ]
        );

        $obj = new SynchronizerMap($this->map, 'Product');

        $this->assertEquals(Collection::class, get_class($obj->getMap()));
        $this->assertEquals(Collection::class, get_class($obj->getExcluded()));
        $this->assertEquals(Collection::class, get_class($obj->getToSync()));
        $this->assertNotEmpty($obj->getMap());
        $this->assertNotEmpty($obj->getToSync());
        $this->assertEmpty($obj->getExcluded());
        $this->assertCount(count($this->map), $obj->getMap());
        $this->assertCount($obj->getMap()->count(), $obj->getToSync());
    }

    /** @test */
    public function add_another_field_to_exclude_after_construct()
    {
        SynchronizerFieldFactory::new()->create(
            [
                'model' => 'Product',
                'field' => 'name'
            ]
        );

        $obj = new SynchronizerMap($this->map, 'Product');

        $this->assertEquals(Collection::class, get_class($obj->getMap()));
        $this->assertEquals(Collection::class, get_class($obj->getExcluded()));
        $this->assertEquals(Collection::class, get_class($obj->getToSync()));
        $this->assertNotEmpty($obj->getMap());
        $this->assertNotEmpty($obj->getToSync());
        $this->assertNotEmpty($obj->getExcluded());
        $this->assertCount(count($this->map), $obj->getMap());
        $this->assertCount($obj->getMap()->count()-1, $obj->getToSync());
        $this->assertCount(1, $obj->getExcluded());

        $obj->markAsExcluded('aged');

        $this->assertCount(count($this->map), $obj->getMap());
        $this->assertCount($obj->getMap()->count()-2, $obj->getToSync());
        $this->assertCount(2, $obj->getExcluded());
    }

    /** @test */
    public function add_field_back_to_sync()
    {
        SynchronizerFieldFactory::new()->create(
            [
                'model' => 'Product',
                'field' => 'name'
            ]
        );

        $obj = new SynchronizerMap($this->map, 'Product');

        $this->assertEquals(Collection::class, get_class($obj->getMap()));
        $this->assertEquals(Collection::class, get_class($obj->getExcluded()));
        $this->assertEquals(Collection::class, get_class($obj->getToSync()));
        $this->assertNotEmpty($obj->getMap());
        $this->assertNotEmpty($obj->getToSync());
        $this->assertNotEmpty($obj->getExcluded());
        $this->assertCount(count($this->map), $obj->getMap());
        $this->assertCount($obj->getMap()->count()-1, $obj->getToSync());
        $this->assertCount(1, $obj->getExcluded());

        $obj->markToSync('name');

        $this->assertCount(count($this->map), $obj->getMap());
        $this->assertCount($obj->getMap()->count(), $obj->getToSync());
        $this->assertEmpty($obj->getExcluded());
    }

    /** @test */
    public function check_constructor_with_not_updateable_field()
    {
        SynchronizerFieldFactory::new()->create(
            [
                'model' => 'Product',
                'field' => 'name',
                'update_empty' => 0,
            ]
        );

        $obj = new SynchronizerMap($this->map, 'Product');

        $this->assertEquals(Collection::class, get_class($obj->getMap()));
        $this->assertEquals(Collection::class, get_class($obj->getExcluded()));
        $this->assertEquals(Collection::class, get_class($obj->getToSync()));
        $this->assertNotEmpty($obj->getMap());
        $this->assertNotEmpty($obj->getToSync());
        $this->assertNotEmpty($obj->getExcluded());
        $this->assertCount(count($this->map), $obj->getMap());
        $this->assertCount($obj->getMap()->count()-1, $obj->getToSync());
        $this->assertCount(1, $obj->getExcluded());
        $this->assertCount(0, $obj->getExcludedNullUpdate());
    }
}
