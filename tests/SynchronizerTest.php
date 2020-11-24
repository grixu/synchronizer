<?php


namespace Grixu\Synchronizer\Tests;


use Grixu\Synchronizer\Models\SynchronizerField;
use Grixu\Synchronizer\Models\SynchronizerLog;
use Grixu\Synchronizer\Synchronizer;
use Grixu\Synchronizer\SynchronizerLogger;
use Grixu\Synchronizer\SynchronizerMap;
use Grixu\Synchronizer\Tests\Helpers\BaseTestCase;
use Grixu\Synchronizer\Tests\Helpers\Product;
use Grixu\Synchronizer\Tests\Helpers\ProductData;
use Grixu\Synchronizer\Tests\Helpers\ProductDataFactory;
use Grixu\Synchronizer\Tests\Helpers\ProductFactory;
use Grixu\Synchronizer\Tests\Helpers\SynchronizerFieldFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class SynchronizerTest
 * @package Grixu\Synchronizer\Tests
 */
class SynchronizerTest extends BaseTestCase
{
    protected array $map;
    protected DataTransferObject $foreign;
    protected Model $local;

    protected function setUp(): void
    {
        parent::setUp();

        $this->map = [
            'name' => 'name',
            'index' => 'index',
            'ean' => 'ean',
            'weight' => 'weight',
        ];

        $this->local = ProductFactory::new()->make();
        $this->local->id = 1;
        $this->foreign = ProductDataFactory::new()->create();
    }

    /** @test */
    public function check_constructor()
    {
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);

        $this->assertEquals(SynchronizerMap::class, get_class($obj->getMap()));
        $this->assertEquals(Product::class, get_class($obj->getLocal()));
        $this->assertEquals(ProductData::class, get_class($obj->getForeign()));
        $this->assertEquals(SynchronizerLogger::class, get_class($obj->getLogger()));
    }

    /** @test */
    public function check_sync_without_excluded_nulls()
    {
        $obj = new Synchronizer($this->map, $this->local, $this->foreign);

        $this->assertNotEquals($this->local->name, $this->foreign->name);
        $this->assertNotEquals($this->local->ean, $this->foreign->ean);
        $this->assertNotEquals($this->local->index, $this->foreign->index);
        $this->assertNotEquals($this->local->weight, $this->foreign->weight);

        SynchronizerLog::query()->delete();
        $this->assertDatabaseCount('synchronizer_logs', 0);

        $obj->sync(false);

        $this->assertDatabaseCount('synchronizer_logs', 1);

        $this->assertEquals($this->foreign->name, $this->local->name);
        $this->assertEquals($this->foreign->ean, $this->local->ean);
        $this->assertEquals($this->foreign->index, $this->local->index);
        $this->assertEquals($this->foreign->weight, $this->local->weight);
    }

    /** @test */
    public function check_sync_with_excluded_nulls()
    {
        SynchronizerField::query()->delete();
        SynchronizerFieldFactory::new()->create(
            [
                'model' => get_class($this->local),
                'field' => 'name',
                'update_empty' => 1
            ]
        );

        $this->local->name = null;

        $obj = new Synchronizer($this->map, $this->local, $this->foreign);

        $this->assertEmpty($this->local->name);
        $this->assertNotEmpty($this->foreign->name);
        $this->assertNotEquals($this->local->ean, $this->foreign->ean);
        $this->assertNotEquals($this->local->index, $this->foreign->index);
        $this->assertNotEquals($this->local->weight, $this->foreign->weight);

        SynchronizerLog::query()->delete();
        $this->assertDatabaseCount('synchronizer_logs', 0);

        $obj->sync();

        $this->assertDatabaseCount('synchronizer_logs', 1);

        $this->assertEquals($this->foreign->name, $this->local->name);
        $this->assertEquals($this->foreign->ean, $this->local->ean);
        $this->assertEquals($this->foreign->index, $this->local->index);
        $this->assertEquals($this->foreign->weight, $this->local->weight);
    }

    /** @test */
    public function check_sync_with_excluded_nulls_but_nothing_on_it()
    {
        SynchronizerField::query()->delete();
        SynchronizerFieldFactory::new()->create(
            [
                'model' => get_class($this->local),
                'field' => 'name',
                'update_empty' => 0
            ]
        );

        $this->local->name = 'check';

        $obj = new Synchronizer($this->map, $this->local, $this->foreign);

        $this->assertNotEquals($this->local->name, $this->foreign->name);
        $this->assertNotEquals($this->local->ean, $this->foreign->ean);
        $this->assertNotEquals($this->local->index, $this->foreign->index);
        $this->assertNotEquals($this->local->weight, $this->foreign->weight);

        SynchronizerLog::query()->delete();
        $this->assertDatabaseCount('synchronizer_logs', 0);

        $obj->sync();

        $this->assertDatabaseCount('synchronizer_logs', 1);

        $this->assertNotEquals($this->foreign->name, $this->local->name);
        $this->assertEquals($this->foreign->ean, $this->local->ean);
        $this->assertEquals($this->foreign->index, $this->local->index);
        $this->assertEquals($this->foreign->weight, $this->local->weight);
    }

    /** @test */
    public function check_sync_with_empty_excluded_field()
    {
        SynchronizerField::query()->delete();
        SynchronizerFieldFactory::new()->create(
            [
                'model' => get_class($this->local),
                'field' => 'name',
                'update_empty' => 0
            ]
        );

        $this->local->name = null;

        $obj = new Synchronizer($this->map, $this->local, $this->foreign);

        $this->assertEmpty($this->local->name);
        $this->assertNotEquals($this->local->name, $this->foreign->name);
        $this->assertNotEquals($this->local->ean, $this->foreign->ean);
        $this->assertNotEquals($this->local->index, $this->foreign->index);
        $this->assertNotEquals($this->local->weight, $this->foreign->weight);

        SynchronizerLog::query()->delete();
        $this->assertDatabaseCount('synchronizer_logs', 0);

        $obj->sync();

        $this->assertDatabaseCount('synchronizer_logs', 1);

        $this->assertNotEmpty($this->foreign->name);
        $this->assertEmpty($this->local->name);
        $this->assertEquals($this->foreign->ean, $this->local->ean);
        $this->assertEquals($this->foreign->index, $this->local->index);
        $this->assertEquals($this->foreign->weight, $this->local->weight);
    }
}
