<?php

namespace Grixu\Synchronizer\Tests\Engine;

use Exception;
use Grixu\SociusModels\Operator\Factories\OperatorDataFactory;
use Grixu\SociusModels\Operator\Models\Branch;
use Grixu\SociusModels\Operator\Models\Operator;
use Grixu\Synchronizer\Engine\BelongsToMany as BelongsToManyEngine;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class BelongsToManyTest extends TestCase
{
    protected Model $localModel;
    protected Model $relatedModel;
    protected BelongsToManyEngine $obj;
    protected Collection $data;

    /** @test */
    public function it_creates_obj_properly_on_belongs_to_many_case()
    {
        $this->makeBelongsToManyCase();

        try {
            $this->obj = new BelongsToManyEngine(Operator::class, $this->data);
            $this->assertTrue(true);
        } catch (Exception $e) {
            ray($e);
            $this->assertTrue(false);
        }
    }

    protected function makeBelongsToManyCase(): void
    {
        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_branches_table.stub';
        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_operators_table.stub';
        require_once __DIR__ . '/../../vendor/grixu/socius-models/migrations/create_operator_branch_pivot_table.stub';
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
        $this->obj = new BelongsToManyEngine(Operator::class, $this->data);
        $this->obj->sync();

        $this->localModel->refresh();
        $this->assertNotEmpty($this->localModel->branches);
    }

    /** @test */
    public function it_return_synced_ids()
    {
        $this->it_sync_belongs_to_many_properly();

        $this->assertNotEmpty($this->obj->getIds());
        $this->assertCount(1, $this->obj->getIds());
    }

    /** @test */
    public function it_handles_even_when_two_different_type_relations()
    {
        $this->makeBelongsToManyCase();
        $this->makeExtraRelationsData();

        $this->obj = new BelongsToManyEngine(Operator::class, $this->data);
        $this->obj->sync();

        $this->localModel->refresh();

        $this->assertNotEmpty($this->localModel->branches);
        $this->assertCount(1, $this->obj->getIds());
    }

    protected function makeExtraRelationsData()
    {
        $this->data->push(
            OperatorDataFactory::new()->create(
                [
                    'relations' => [
                        [
                            'foreignClass' => Branch::class,
                            'relation' => 'branches',
                            'foreignField' => 'xl_id',
                            'type' => BelongsTo::class,
                            'foreignKeys' => (int)$this->relatedModel->xl_id,
                        ]
                    ]
                ]
            )->toArray()
        );

        $this->data->push(
            OperatorDataFactory::new()->create(
                [
                    'relations' => [
                        [
                            'foreignClass' => Branch::class,
                            'relation' => 'branches',
                            'foreignField' => 'xl_id',
                            'type' => BelongsToMany::class,
                            'foreignKeys' => (int)$this->relatedModel->xl_id,
                        ],
                        [
                            'foreignClass' => Branch::class,
                            'relation' => 'branches',
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
