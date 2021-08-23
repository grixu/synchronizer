<?php

namespace Grixu\Synchronizer\Tests\Engine;

use Grixu\SociusModels\Operator\Factories\OperatorDataFactory;
use Grixu\SociusModels\Operator\Models\Branch;
use Grixu\SociusModels\Operator\Models\Operator;
use Grixu\SociusModels\Operator\Models\OperatorRole;
use Grixu\SociusModels\Product\Factories\ProductDataFactory;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Engine\Contracts\Engine;
use Grixu\Synchronizer\Engine\Map\MapFactory;
use Grixu\Synchronizer\Engine\Model as ModelEngine;
use Grixu\Synchronizer\Engine\Transformer\Transformer;
use Grixu\Synchronizer\Tests\Helpers\FakeSyncConfig;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

        $this->obj = new ModelEngine(FakeSyncConfig::makeWithCustomModel(Product::class), $this->data);
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
        $this->obj = new ModelEngine(FakeSyncConfig::makeWithCustomModel(Product::class), $this->data);
        $this->assertDatabaseCount('products', 1);

        $this->obj->sync($this->transformer);

        $this->assertDatabaseCount('products', 1);
    }

    /** @test */
    public function it_reset_checksum_when_relations_found_in_dataset()
    {
        SyncConfig::setInstance(FakeSyncConfig::makeWithCustomModel(Operator::class));
        $this->makeComplicatedCase();
        $this->obj = new ModelEngine(SyncConfig::getInstance(), $this->data);
        $this->assertDatabaseCount('operators', 1);

        $this->obj->sync($this->transformer);

        $this->assertDatabaseCount('operators', 1);

        $this->localModel->refresh();
        $this->assertEmpty($this->localModel->checksum);
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
                            'foreignKeys' => (int)rand(100,999),
                        ],
                        [
                            'foreignClass' => OperatorRole::class,
                            'relation' => 'role',
                            'foreignField' => 'xl_id',
                            'type' => BelongsTo::class,
                            'foreignKeys' => (int)rand(100,999),
                        ]
                    ]
                ]
            )->toArray()
        );

        $map = MapFactory::makeFromArray($this->data->first());
        $this->transformer = new Transformer($map);
    }

}
