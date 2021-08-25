<?php

namespace Grixu\Synchronizer\Tests\Engine;

use Exception;
use Grixu\SociusModels\Operator\Factories\OperatorDataFactory;
use Grixu\SociusModels\Operator\Models\Branch;
use Grixu\SociusModels\Operator\Models\Operator;
use Grixu\SociusModels\Operator\Models\OperatorRole;
use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Brand;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\SociusModels\Product\Models\ProductType;
use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Engine\BelongsTo as BelongsToEngine;
use Grixu\Synchronizer\Engine\Contracts\Engine;
use Grixu\Synchronizer\Engine\Map\MapFactory;
use Grixu\Synchronizer\Engine\Transformer\Transformer;
use Grixu\Synchronizer\Tests\Helpers\FakeSyncConfig;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class BelongsToTest extends TestCase
{
    protected Model $localModel;
    protected Model $relatedModel;
    protected Model $secondRelatedModel;
    protected Engine $obj;
    protected Collection $data;
    protected Transformer $transformer;
    protected SyncConfig $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = FakeSyncConfig::makeWithCustomModel(Product::class);
        SyncConfig::setInstance($this->config);
    }

    /** @test */
    public function it_creates_obj_properly()
    {
        $this->makeBelongsToCase();

        try {
            $this->obj = new BelongsToEngine($this->config, $this->data);
            $this->assertTrue(true);
        } catch (Exception $e) {
            ray($e);
            $this->assertTrue(false);
        }
    }

    protected function makeBelongsToCase(): void
    {
        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_brands_table.stub';
        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_product_types_table.stub';
        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_products_table.stub';
        (new \CreateBrandsTable())->up();
        (new \CreateProductTypesTable())->up();
        (new \CreateProductsTable())->up();

        $this->localModel = Product::factory()->create();
        $this->relatedModel = Brand::factory()->create();
        $this->secondRelatedModel = ProductType::factory()->create();

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
                        ],
                        [
                            'foreignClass' => ProductType::class,
                            'relation' => 'productType',
                            'foreignField' => 'xl_id',
                            'type' => BelongsTo::class,
                            'foreignKeys' => (int)$this->secondRelatedModel->xl_id,
                        ],
                    ],
                ]
            )->toArray()
        );

        $map = MapFactory::makeFromArray($this->data->first());
        $this->transformer = new Transformer($map);

        $this->assertEmpty($this->localModel->brand_id);
        $this->assertEmpty($this->localModel->product_type_id);
    }

    /** @test */
    public function it_sync_belongs_to_properly()
    {
        $this->makeBelongsToCase();
        $this->obj = new BelongsToEngine($this->config, $this->data);
        $this->obj->sync($this->transformer);

        $this->localModel->refresh();

        $this->assertNotEmpty($this->localModel->brand_id);
        $this->assertNotEmpty($this->localModel->product_type_id);
    }

    /** @test */
    public function it_sync_belongs_to_properly_even_when_all_entries_have_all_relations_attached()
    {
        $this->makeBelongsToCase();
        $tempModel = Product::factory()->create();
        $this->data->push(
            ProductDataFactory::new()->create(
                [
                    'xlId' => $tempModel->xl_id,
                    'relations' => [
                        [
                            'foreignClass' => Brand::class,
                            'relation' => 'brand',
                            'foreignField' => 'xl_id',
                            'type' => BelongsTo::class,
                            'foreignKeys' => (int)$this->relatedModel->xl_id,
                        ],
                    ],
                ]
            )->toArray()
        );

        $this->obj = new BelongsToEngine($this->config, $this->data);
        $this->obj->sync($this->transformer);

        $tempModel->refresh();
        $this->assertNotEmpty($tempModel->brand_id);
        $this->assertEmpty($tempModel->product_type_id);
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
        SyncConfig::setInstance(FakeSyncConfig::makeWithCustomModel(Operator::class));
        $this->makeComplicatedCase();

        $this->obj = new BelongsToEngine(SyncConfig::getInstance(), $this->data);
        $this->obj->sync($this->transformer);

        $this->localModel->refresh();

        $this->assertNotEmpty($this->localModel->operator_role_id);
        $this->assertCount(1, $this->obj->getIds());
    }

    protected function makeComplicatedCase()
    {
        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_branches_table.stub';
        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_operator_roles_table.stub';
        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_operators_table.stub';
        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_operator_branch_pivot_table.stub';
        (new \CreateBranchesTable())->up();
        (new \CreateOperatorRolesTable())->up();
        (new \CreateOperatorsTable())->up();
        (new \CreateOperatorBranchPivotTable())->up();

        $this->localModel = Operator::factory()->create();
        $this->relatedModel = OperatorRole::factory()->create();
        $this->secondRelatedModel = Branch::factory()->create();

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
                            'foreignKeys' => (int)$this->secondRelatedModel->xl_id,
                        ],
                        [
                            'foreignClass' => OperatorRole::class,
                            'relation' => 'role',
                            'foreignField' => 'xl_id',
                            'type' => BelongsTo::class,
                            'foreignKeys' => (int)$this->relatedModel->xl_id,
                        ],
                    ],
                ]
            )->toArray()
        );

        $map = MapFactory::makeFromArray($this->data->first());
        $this->transformer = new Transformer($map);

        $this->assertEmpty($this->localModel->branches);
        $this->assertEmpty($this->localModel->operator_role_id);
    }

    /** @test */
    public function it_handles_no_rel_entries()
    {
        SyncConfig::setInstance(FakeSyncConfig::makeWithCustomModel(Operator::class));
        $this->makeComplicatedCase();

        $this->data->push(OperatorDataFactory::new()->create()->toArray());

        $this->obj = new BelongsToEngine(SyncConfig::getInstance(), $this->data);
        $this->obj->sync($this->transformer);

        $this->localModel->refresh();

        $this->assertNotEmpty($this->localModel->operator_role_id);
        $this->assertCount(1, $this->obj->getIds());
    }

    /** @test */
    public function it_exit_gently_when_is_nothing_to_sync()
    {
        $this->makeBelongsToCase();
        $this->data = collect();
        $this->data->push(
            ProductDataFactory::new()->create()->toArray()
        );

        $this->obj = new BelongsToEngine($this->config, $this->data);
        $this->obj->sync($this->transformer);

        $this->localModel->refresh();
        $this->assertEmpty($this->localModel->brand_id);
    }

    /** @test */
    public function it_exit_gently_when_related_obj_not_exists()
    {
        $this->makeBelongsToCase();
        $this->data->push(
            ProductDataFactory::new()->create(
                [
                    'relations' => [
                        [
                            'foreignClass' => Brand::class,
                            'relation' => 'brand',
                            'foreignField' => 'xl_id',
                            'type' => BelongsTo::class,
                            'foreignKeys' => (int)$this->relatedModel->xl_id+1,
                        ],
                    ],
                ]
            )->toArray()
        );

        $this->obj = new BelongsToEngine($this->config, $this->data);
        $this->obj->sync($this->transformer);

        $this->assertCount($this->data->count(), $this->obj->getIds());
        $this->assertDatabaseCount('products', $this->data->count());

        $firstModel = Product::query()->where('xl_id', $this->data[0]['xlId'])->first();
        $this->assertNotEmpty($firstModel);
        $this->assertNotEmpty($firstModel->brand_id);

        $secondModel = Product::query()->where('xl_id', $this->data[1]['xlId'])->first();
        $this->assertNotEmpty($secondModel);
        $this->assertEmpty($secondModel->brand_id);
        $this->assertEmpty($secondModel->{config('synchronizer.checksum.field')});
    }
}
