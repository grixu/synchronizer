<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\SociusModels\Product\DataTransferObjects\ProductDataCollection;
use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\CollectionSynchronizer;
use Grixu\Synchronizer\Tests\Helpers\MigrateProductsTrait;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class CollectionSynchronizerTest extends TestCase
{
    use MigrateProductsTrait;
    use RefreshDatabase;

    protected ProductDataCollection $dtoCollection;
    protected CollectionSynchronizer $obj;

    protected function slackConfig($app)
    {
        $app['config']->set('synchronizer.send_slack_sum_up', true);
        $app['config']->set('logging.slack.url', 'http://slack.com');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateProducts();

        $this->dtoCollection = new ProductDataCollection(
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
            Product::factory()->create(['xlId' => $dto->xlId]);
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

        $this->dtoCollection = new ProductDataCollection(
            array_merge(
                $this->dtoCollection->items(),
                ProductDataFactory::times(10)->create()->toArray()
            )
        );

        ray($this->dtoCollection);

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
}
