<?php

namespace Grixu\Synchronizer\Tests;

use Exception;
use Grixu\SociusModels\Operator\Factories\OperatorDataFactory;
use Grixu\SociusModels\Operator\Models\Branch;
use Grixu\SociusModels\Operator\Models\Operator;
use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Brand;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\RelationsSynchronizer;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class RelationsSynchronizerTest extends TestCase
{
    protected Model $localModel;
    protected Model $relatedModel;
    protected RelationsSynchronizer $obj;
    protected Collection $data;

    /** @test */
    public function it_creates_obj_properly()
    {
        $this->makeBelongsToCase();

        try {
            $this->obj = new RelationsSynchronizer(Product::class, $this->data);
            $this->assertTrue(true);
        } catch (Exception $e) {
            ray($e);
            $this->assertTrue(false);
        }
    }

    protected function makeBelongsToCase(): void
    {
        require_once __DIR__ . '/../vendor/grixu/socius-models/migrations/create_brands_table.stub';
        require_once __DIR__ . '/../vendor/grixu/socius-models/migrations/create_products_table.stub';
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

        $this->assertEmpty($this->localModel->brand_id);
    }

    /** @test */
    public function it_sync_belongs_to_properly()
    {
        $this->makeBelongsToCase();
        $this->obj = new RelationsSynchronizer(Product::class, $this->data);
        $this->obj->syncBelongsTo();

        $this->localModel->refresh();

        $this->assertNotEmpty($this->localModel->brand_id);
    }

    /** @test */
    public function it_return_attached_ids()
    {
        $this->it_sync_belongs_to_properly();

        $this->assertNotEmpty($this->obj->getAttachedIds());
        $this->assertCount(1, $this->obj->getAttachedIds());
    }

    /** @test */
    public function it_creates_obj_properly_on_belongs_to_many_case()
    {
        $this->makeBelongsToManyCase();

        try {
            $this->obj = new RelationsSynchronizer(Operator::class, $this->data);
            $this->assertTrue(true);
        } catch (Exception $e) {
            ray($e);
            $this->assertTrue(false);
        }
    }

    protected function makeBelongsToManyCase(): void
    {
        require_once __DIR__ . '/../vendor/grixu/socius-models/migrations/create_branches_table.stub';
        require_once __DIR__ . '/../vendor/grixu/socius-models/migrations/create_operators_table.stub';
        require_once __DIR__ . '/../vendor/grixu/socius-models/migrations/create_operator_branch_pivot_table.stub';
        (new \CreateBranchesTable())->up();
        (new \CreateOperatorsTable())->up();
        (new \CreateOperatorBranchPivotTable())->up();

        $this->localModel = Operator::factory()->create();
        $this->relatedModel = Branch::factory()->create();
        $this->data = collect();
        $this->data->push(
            OperatorDataFactory::new()->create(
                [
                    'xlId' => $this->localModel->xl_id,
                    'relations' => [
                        [
                            'foreignClass' => Branch::class,
                            'relation' => 'branches',
                            'foreignField' => 'xl_id',
                            'type' => BelongsToMany::class,
                            'foreignKeys' => [(int)$this->relatedModel->xl_id],
                        ]
                    ]
                ]
            )->toArray()
        );

        $this->assertEmpty($this->localModel->branches);
    }

    /** @test */
    public function it_sync_belongs_to_many_properly()
    {
        $this->makeBelongsToManyCase();
        $this->obj = new RelationsSynchronizer(Operator::class, $this->data);
        $this->obj->syncBelongsToMany();

        $this->localModel->refresh();
        $this->assertNotEmpty($this->localModel->branches);
    }

    /** @test */
    public function it_return_synced_ids()
    {
        $this->it_sync_belongs_to_many_properly();

        $this->assertNotEmpty($this->obj->getSyncedIds());
        $this->assertCount(1, $this->obj->getSyncedIds());
    }
}
