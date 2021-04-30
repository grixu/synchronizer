<?php

namespace Grixu\Synchronizer\Tests;

use Exception;
use Grixu\RelationshipDataTransferObject\RelationshipDataCollection;
use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Brand;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\CollectionSynchronizer;
use Grixu\Synchronizer\Exceptions\EmptyForeignKeyInDto;
use Grixu\Synchronizer\Tests\Helpers\MigrateProductsTrait;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CollectionSynchronizerTest extends TestCase
{
    use MigrateProductsTrait;
    use RefreshDatabase;

    protected Collection $dtoCollection;
    protected CollectionSynchronizer $obj;

    protected function slackConfig($app)
    {
        $app['config']->set('synchronizer.sync.send_notification', true);
        $app['config']->set('logging.channels.slack.url', 'http://slack.com');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateProducts();

        $this->dtoCollection = new Collection(
            ProductDataFactory::times(10)->create()->toArray()
        );

        $this->createObj();
    }

    protected function createObj()
    {
        $this->obj = new CollectionSynchronizer($this->dtoCollection, Product::class, 'xlId');
    }

    /** @test */
    public function it_creates_itself()
    {
        $this->assertEquals(CollectionSynchronizer::class, get_class($this->obj));
    }

    /** @test */
    public function sync_collection_with_no_models()
    {
        $this->assertDatabaseCount('products', 0);
        $this->obj->sync();
        $this->assertDatabaseCount('products', 10);
    }

    /** @test */
    public function it_sync_collection_with_all_models()
    {
        $this->createModelsBasedOnDto();
        $this->assertCountAndChecksums(10, 0);
        $this->obj->sync();
        $this->assertCountAndChecksums(10, 10);
    }

    protected function createModelsBasedOnDto()
    {
        foreach ($this->dtoCollection as $dto) {
            Product::factory()->create(['xl_id' => $dto->xlId]);
        }
    }

    protected function assertCountAndChecksums(int $count, int $checksumCount)
    {
        $this->assertDatabaseCount('products', $count);
        $this->assertEquals($checksumCount, Product::query()->whereNotNull('checksum')->count());
    }

    /** @test */
    public function it_sync_collection_with_some_models_some_not()
    {
        $this->createModelsBasedOnDto();

        $this->dtoCollection = new Collection(
            array_merge(
                $this->dtoCollection->toArray(),
                ProductDataFactory::times(10)->create()->toArray()
            )
        );

        $this->assertCountAndChecksums(10, 0);
        $this->createObj();
        $this->obj->sync();

        $this->assertCountAndChecksums(20, 20);
    }

    /**
     * @test
     * @environment-setup slackConfig
     */
    public function push_log_to_slack()
    {
        Log::shouldReceive('channel')->with('slack')->once()->andReturnSelf();
        Log::shouldReceive('notice')->once()->andReturnNull();

        $this->it_sync_collection_with_all_models();
    }

    /** @test */
    public function empty_foreign_keys_throws_exception_in_dto()
    {
        try {
            $this->obj = new CollectionSynchronizer($this->dtoCollection, Product::class, 'some_key');
            $this->assertTrue(false);
        } catch (EmptyForeignKeyInDto) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function empty_foreign_keys_throws_exception_in_array()
    {
        $this->dtoCollection = $this->dtoCollection->map(fn($item) => $item->toArray());

        try {
            $this->obj = new CollectionSynchronizer($this->dtoCollection, Product::class, 'some_key');
            $this->assertTrue(false);
        } catch (EmptyForeignKeyInDto) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function is_making_map_from_passed_array()
    {
        $this->createObj();
        $this->assertDatabaseCount('products', 0);
        $this->obj->sync(
            [
                'name' => 'name',
                'index' => 'index',
                'ean' => 'ean',
                'measureUnit' => 'measure_unit',
                'taxGroup' => 'tax_group',
                'taxValue' => 'tax_value',
                'weight' => 'weight',
                'eshop' => 'eshop',
                'price' => 'price',
                'eshopPrice' => 'eshop_price',
                'xlId' => 'xl_id',
                'syncTs' => 'sync_ts'
            ]
        );
        $this->assertDatabaseCount('products', 10);
    }

    /** @test */
    public function is_making_map_from_array_in_collection()
    {
        $this->dtoCollection = collect(
            [
                ProductDataFactory::new()->create()->toArray()
            ]
        );

        $this->createObj();
        $this->assertDatabaseCount('products', 0);
        $this->obj->sync();
        $this->assertDatabaseCount('products', 1);
    }

    /** @test */
    public function syncs_relationships_using_dto()
    {
        $brand = Brand::factory()->create();

        $this->dtoCollection = new Collection(
            [
                ProductDataFactory::new()->create(
                    [
                        'relationships' => [
                            [
                                'localClass' => Product::class,
                                'foreignClass' => Brand::class,
                                'localRelationshipName' => 'brand',
                                'foreignRelatedFieldName' => 'xl_id',
                                'type' => BelongsTo::class,
                                'foreignKey' => $brand->xlId,
                            ]
                        ],
                    ]
                )
            ]
        );
        $this->createObj();

        $this->assertDatabaseCount('products', 0);
        $this->obj->sync();
        $this->assertDatabaseCount('products', 1);
    }

    /** @test */
    public function syncs_relationships_using_array()
    {
        $brand = Brand::factory()->create();

        $arrEntry = ProductDataFactory::new()->create()->toArray();
        $arrEntry['relationships'] = RelationshipDataCollection::create(
            [
                [
                    'localClass' => Product::class,
                    'foreignClass' => Brand::class,
                    'localRelationshipName' => 'brand',
                    'foreignRelatedFieldName' => 'xl_id',
                    'type' => BelongsTo::class,
                    'foreignKey' => $brand->xlId,
                ]
            ]
        );

        $this->dtoCollection = new Collection(
            [
                $arrEntry
            ]
        );
        $this->createObj();

        $this->assertDatabaseCount('products', 0);
        $this->obj->sync();
        $this->assertDatabaseCount('products', 1);
    }

    /** @test */
    public function it_throws_exception_on_empty_collection_given()
    {
        $this->dtoCollection = new Collection();

        try {
            $this->createObj();
            $this->assertTrue(false);
        } catch (Exception) {
            $this->assertTrue(true);
        }
    }
}
