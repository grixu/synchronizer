<?php

namespace Grixu\Synchronizer\Tests\Engine;

use Exception;
use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Brand;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Contracts\Engine;
use Grixu\Synchronizer\Engine\BelongsTo as BelongsToEngine;
use Grixu\Synchronizer\MapFactory;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Grixu\Synchronizer\Transformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class BelongsToTest extends TestCase
{
    protected Model $localModel;
    protected Model $relatedModel;
    protected Engine $obj;
    protected Collection $data;
    protected Transformer $transformer;

    /** @test */
    public function it_creates_obj_properly()
    {
        $this->makeBelongsToCase();

        try {
            $this->obj = new BelongsToEngine($this->data, 'xlId', Product::class);
            $this->assertTrue(true);
        } catch (Exception $e) {
            ray($e);
            $this->assertTrue(false);
        }
    }

    protected function makeBelongsToCase(): void
    {
        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_brands_table.stub';
        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_products_table.stub';
        (new \CreateBrandsTable())->up();
        (new \CreateProductsTable())->up();

        $this->localModel = Product::factory()->create();
        $this->relatedModel = Brand::factory()->create();
        $this->data = collect();
        $this->data->push(
            ProductDataFactory::new()->create(
                [
                    'xlId' => $this->localModel->xl_id,
                    'relations' => [
                        [
                            'foreignClass' => Brand::class,
                            'relation' => 'brand',
                            'foreignField' => 'xl_id',
                            'type' => BelongsTo::class,
                            'foreignKeys' => (int)$this->relatedModel->xl_id,
                        ]
                    ]
                ]
            )->toArray()
        );

        $map = MapFactory::makeFromArray($this->data->first(), Product::class);
        $this->transformer = new Transformer($map);

        $this->assertEmpty($this->localModel->brand_id);
    }

    /** @test */
    public function it_sync_belongs_to_properly()
    {
        $this->makeBelongsToCase();
        $this->obj = new BelongsToEngine($this->data, 'xlId', Product::class);
        $this->obj->sync($this->transformer);

        $this->localModel->refresh();

        $this->assertNotEmpty($this->localModel->brand_id);
    }

    /** @test */
    public function it_return_attached_ids()
    {
        $this->it_sync_belongs_to_properly();

        $this->assertNotEmpty($this->obj->getIds());
        $this->assertCount(1, $this->obj->getIds());
    }

    /** @test */
    public function it_handles_even_when_two_different_type_relations()
    {
        $this->makeBelongsToCase();
        $this->makeExtraRelationsData();

        $this->obj = new BelongsToEngine($this->data, 'xlId', Product::class);
        $this->obj->sync($this->transformer);

        $this->localModel->refresh();

        $this->assertNotEmpty($this->localModel->brand_id);
        $this->assertCount(3, $this->obj->getIds());
    }

    protected function makeExtraRelationsData()
    {
        $this->data->push(
            ProductDataFactory::new()->create(
                [
                    'relations' => [
                        [
                            'foreignClass' => Brand::class,
                            'relation' => 'brand',
                            'foreignField' => 'xl_id',
                            'type' => BelongsToMany::class,
                            'foreignKeys' => (int)$this->relatedModel->xl_id,
                        ]
                    ]
                ]
            )->toArray()
        );

        $this->data->push(
            ProductDataFactory::new()->create(
                [
                    'relations' => [
                        [
                            'foreignClass' => Brand::class,
                            'relation' => 'brand',
                            'foreignField' => 'xl_id',
                            'type' => BelongsToMany::class,
                            'foreignKeys' => (int)$this->relatedModel->xl_id,
                        ],
                        [
                            'foreignClass' => Brand::class,
                            'relation' => 'brand',
                            'foreignField' => 'xl_id',
                            'type' => BelongsTo::class,
                            'foreignKeys' => (int)$this->relatedModel->xl_id,
                        ]
                    ]
                ]
            )->toArray()
        );
    }
}
