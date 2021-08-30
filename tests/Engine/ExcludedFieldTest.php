<?php

namespace Grixu\Synchronizer\Tests\Engine;

use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Engine\Config\EngineConfig;
use Grixu\Synchronizer\Engine\Contracts\Engine;
use Grixu\Synchronizer\Engine\ExcludedField;
use Grixu\Synchronizer\Engine\Map\MapFactory;
use Grixu\Synchronizer\Engine\Transformer\Transformer;
use Grixu\Synchronizer\Tests\Helpers\FakeEngineConfig;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Support\Collection;

class ExcludedFieldTest extends TestCase
{
    protected Product $model;
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
                'index' => null,
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

        EngineConfig::setInstance(FakeEngineConfig::make(model: Product::class, fields: ['index'=>['fillable']]));
        $map = MapFactory::makeFromArray($this->data->first());
        $this->transformer = new Transformer($map);

        $this->assertCount(1, $map->getUpdatableOnNullFields());

        $this->obj = new ExcludedField(EngineConfig::getInstance(), $this->data);
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
