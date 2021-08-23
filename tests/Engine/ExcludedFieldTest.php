<?php

namespace Grixu\Synchronizer\Tests\Engine;

use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Engine\Contracts\Engine;
use Grixu\Synchronizer\Engine\ExcludedField;
use Grixu\Synchronizer\Engine\Map\MapFactory;
use Grixu\Synchronizer\Engine\Transformer\Transformer;
use Grixu\Synchronizer\Engine\Models\ExcludedField as ExcludedFieldModel;
use Grixu\Synchronizer\Tests\Helpers\FakeSyncConfig;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Support\Collection;

class ExcludedFieldTest extends TestCase
{
    protected Product $model;
    protected ExcludedFieldModel $excludedField;
    protected Engine $obj;
    protected Collection $data;
    protected Transformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_products_table.stub';
        (new \CreateProductsTable())->up();

        $this->model = Product::factory()->create(
            [
                'index' => null
            ]
        );

        $this->excludedField = ExcludedFieldModel::create(
            [
                'model' => Product::class,
                'update_empty' => true,
                'field' => 'index'
            ]
        );

        $this->data = collect();
        $this->data->push(
            ProductDataFactory::new()->create(
                [
                    'xlId' => $this->model->xl_id,
                ]
            )->toArray()
        );

        SyncConfig::setInstance(FakeSyncConfig::makeWithCustomModel(Product::class));
        $map = MapFactory::makeFromArray($this->data->first());
        $this->transformer = new Transformer($map);

        $this->assertCount(1, $map->getUpdatableOnNullFields());

        $this->obj = new ExcludedField(SyncConfig::getInstance(), $this->data);
    }

    /** @test */
    public function it_constructs_properly()
    {
        $this->assertNotEmpty($this->obj);
    }

    /** @test */
    public function it_sync_empty_field_properly()
    {
        $this->assertEmpty($this->model->index);

        $this->obj->sync($this->transformer);

        $this->model->refresh();
        $this->assertNotEmpty($this->model->index);
    }
}
