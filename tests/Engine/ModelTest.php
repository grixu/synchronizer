<?php

namespace Grixu\Synchronizer\Tests\Engine;

use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Engine\Contracts\Engine;
use Grixu\Synchronizer\Engine\Map\MapFactory;
use Grixu\Synchronizer\Engine\Model as ModelEngine;
use Grixu\Synchronizer\Engine\Transformer\Transformer;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ModelTest extends TestCase
{
    protected Model $localModel;
    protected Engine $obj;
    protected Collection $data;
    protected Transformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_products_table.stub';
        (new \CreateProductsTable())->up();

        $this->localModel = Product::factory()->create();
        $this->data = collect();
        $this->data->push(
            ProductDataFactory::new()->create(
                [
                    'xlId' => $this->localModel->xl_id,
                ]
            )->toArray(),
            ProductDataFactory::new()->create()->toArray(),
        );

        $map = MapFactory::makeFromArray($this->data->first(), Product::class);
        $this->transformer = new Transformer($map);

        $this->obj = new ModelEngine($this->data, 'xlId', Product::class);
    }

    /** @test */
    public function it_sync_data()
    {
        $this->assertDatabaseCount('products', 1);

        $this->obj->sync($this->transformer);

        $this->assertDatabaseCount('products', 2);
    }

    /** @test */
    public function it_exit_gently_when_it_nothing_to_sync()
    {
        $this->data = collect();
        $this->obj = new ModelEngine($this->data, 'xlId', Product::class);
        $this->assertDatabaseCount('products', 1);

        $this->obj->sync($this->transformer);

        $this->assertDatabaseCount('products', 1);
    }
}
