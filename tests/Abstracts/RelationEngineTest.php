<?php

namespace Grixu\Synchronizer\Tests\Abstracts;

use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Brand;
use Grixu\SociusModels\Product\Models\Category;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Contracts\Engine;
use Grixu\Synchronizer\Engine\BelongsTo as BelongsToEngine;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class RelationEngineTest extends TestCase
{
    protected Model $localModel;
    protected Model $relatedModel;
    protected Engine $obj;
    protected Collection $data;

    protected function setUp(): void
    {
        parent::setUp();

        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_brands_table.stub';
        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_products_table.stub';
        (new \CreateBrandsTable())->up();
        (new \CreateProductsTable())->up();

        $this->localModel = Product::factory()->create();
        $this->relatedModel = Brand::factory()->create();
        $this->data = collect();
    }

    /** @test */
    public function it_throws_exception_on_non_existing_method()
    {
        $this->makeBrokenRelationCase();

        try {
            $this->obj = new BelongsToEngine(Product::class, $this->data);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            ray($e);
            $this->assertTrue(true);
        }
    }

    protected function makeBrokenRelationCase(): void
    {
        $this->data->push(
            ProductDataFactory::new()->create(
                [
                    'xlId' => $this->localModel->xl_id,
                    'relations' => [
                        [
                            'foreignClass' => Brand::class,
                            'relation' => 'brands',
                            'foreignField' => 'xl_id',
                            'type' => BelongsTo::class,
                            'foreignKeys' => (int)$this->relatedModel->xl_id,
                        ]
                    ]
                ]
            )->toArray()
        );
    }

    /** @test */
    public function it_throws_exception_on_non_relation_method()
    {
        $this->makeNotRelationCase();

        try {
            $this->obj = new BelongsToEngine(Product::class, $this->data);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            ray($e);
            $this->assertTrue(true);
        }
    }

    protected function makeNotRelationCase(): void
    {
        $this->data->push(
            ProductDataFactory::new()->create(
                [
                    'xlId' => $this->localModel->xl_id,
                    'relations' => [
                        [
                            'foreignClass' => Brand::class,
                            'relation' => 'getCasts',
                            'foreignField' => 'xl_id',
                            'type' => BelongsTo::class,
                            'foreignKeys' => (int)$this->relatedModel->xl_id,
                        ]
                    ]
                ]
            )->toArray()
        );
    }

    /** @test */
    public function it_throws_exception_on_other_model_relation()
    {
        $this->makeNotThisModelCase();

        try {
            $this->obj = new BelongsToEngine(Product::class, $this->data);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            ray($e);
            $this->assertTrue(true);
        }
    }

    protected function makeNotThisModelCase(): void
    {
        $this->data->push(
            ProductDataFactory::new()->create(
                [
                    'xlId' => $this->localModel->xl_id,
                    'relations' => [
                        [
                            'foreignClass' => Category::class,
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
