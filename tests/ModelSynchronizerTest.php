<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\SociusModels\Product\DataTransferObjects\ProductData;
use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Events\ModelCreatedEvent;
use Grixu\Synchronizer\Events\ModelSynchronizedEvent;
use Grixu\Synchronizer\MapFactory;
use Grixu\Synchronizer\Models\Log;
use Grixu\Synchronizer\ModelSynchronizer;
use Grixu\Synchronizer\Tests\Helpers\MigrateProductsTrait;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

class ModelSynchronizerTest extends TestCase
{
    use MigrateProductsTrait;

    protected ProductData $dto;
    protected Product $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateProducts();
        $this->dto = ProductDataFactory::new()->create();
        $this->model = Product::factory()->create(
            [
                'brandId' => null,
                'productTypeId' => null
            ]
        );
    }

    /** @test */
    public function it_creates_itself_with_map()
    {
        $map = MapFactory::makeFromDto($this->dto, get_class($this->model));
        $obj = new ModelSynchronizer($this->dto, $this->model, $map);

        $this->assertEquals(ModelSynchronizer::class, get_class($obj));
    }

    /** @test */
    public function it_creates_itself_without_map()
    {
        $obj = new ModelSynchronizer($this->dto, $this->model);
        $this->assertEquals(ModelSynchronizer::class, get_class($obj));
    }

    /** @test */
    public function it_creates_itself_without_model()
    {
        $obj = new ModelSynchronizer($this->dto, Product::class);
        $this->assertEquals(ModelSynchronizer::class, get_class($obj));
    }

    /** @test */
    public function it_creates_model()
    {
        $obj = new ModelSynchronizer($this->dto, Product::class);
        $model = $obj->sync();

        $this->assertEquals(Product::class, get_class($model));
        $this->assertTransfer($model);
    }

    protected function assertTransfer($model)
    {
        foreach ($this->dto as $key => $value) {
            if (is_object($value) && get_class($value) === Carbon::class) {
                $this->assertEquals($value->timestamp, $model->$key->timestamp);
            } else {
                $this->assertEquals($value, $model->$key);
            }
        }

        $this->assertNotEmpty($model->checksum);
    }

    /** @test */
    public function it_updates_model()
    {
        $obj = new ModelSynchronizer($this->dto, $this->model);
        $model = $obj->sync();

        $this->assertTransfer($model);
    }

    /** @test */
    public function it_creates_log()
    {
        $this->assertDatabaseCount('synchronizer_logs', 0);
        $obj = new ModelSynchronizer($this->dto, $this->model);
        $obj->sync();
        $this->assertDatabaseCount('synchronizer_logs', 1);
    }

    /** @test */
    public function it_not_creating_log_from_timestamps()
    {
        $this->assertDatabaseCount('synchronizer_logs', 0);
        $obj = new ModelSynchronizer($this->dto, $this->model);
        $obj->sync();
        $this->assertDatabaseCount('synchronizer_logs', 1);

        $model = Log::query()
            ->where(
                [
                    ['model', '=', get_class($this->model)],
                    ['model_id', '=', $this->model->id]
                ]
            )->first();

        foreach ($model->log as $log) {
            $this->assertEquals(
                false,
                in_array($log['modelField'], config('synchronizer.timestamps'))
            );
        }
    }

    /** @test */
    public function it_firing_event_when_model_is_created()
    {
        Event::fake();

        $this->it_creates_model();

        Event::assertDispatched(ModelCreatedEvent::class);
        Event::assertNotDispatched(ModelSynchronizedEvent::class);
    }

    /** @test */
    public function it_firing_event_when_model_is_synced()
    {
        Event::fake();

        $this->it_updates_model();

        Event::assertDispatched(ModelSynchronizedEvent::class);
        Event::assertNotDispatched(ModelCreatedEvent::class);
    }

    /**
     * @test
     * @environment-setup useChecksumTimestampExcluded
     */
    public function it_not_syncing_timestamp_when_excluded_option_is_on()
    {
        $obj = new ModelSynchronizer($this->dto, $this->model);
        $model = $obj->sync();
        $this->model = $model;

        $obj = new ModelSynchronizer($this->dto, $model);
        $obj->sync();

        $this->assertTrue($model === $this->model);
    }

    protected function useChecksumTimestampExcluded($app)
    {
        $app->config->set('synchronizer.checksum_timestamps_excluded', true);
    }

    /**
     * @test
     * @environment-setup useChecksumTimestampExcluded
     */
    public function it_sync_timestamp_when_excluded_option_is_on_but_not_excluded_field_in_dto_changed()
    {
        $this->it_updates_model();
    }

}
