<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\SociusModels\Operator\Factories\OperatorDataFactory;
use Grixu\SociusModels\Operator\Models\Branch;
use Grixu\SociusModels\Operator\Models\Operator;
use Grixu\SociusModels\Operator\Models\OperatorRole;
use Grixu\Synchronizer\Synchronizer;
use Grixu\Synchronizer\Config\SyncConfig;
use Grixu\Synchronizer\Events\SynchronizerEvent;
use Grixu\Synchronizer\Tests\Helpers\FakeLoader;
use Grixu\Synchronizer\Tests\Helpers\FakeParser;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class SynchronizerTest extends TestCase
{
    use RefreshDatabase;

    protected array $data = [];
    protected SyncConfig $config;
    protected string $batchId;
    protected Synchronizer $obj;

    protected Branch $branch;
    protected OperatorRole $operatorRole;

    protected function setUp(): void
    {
        parent::setUp();

        require_once __DIR__ . '/../vendor/grixu/socius-models/migrations/create_branches_table.stub';
        (new \CreateBranchesTable())->up();

        require_once __DIR__ . '/../vendor/grixu/socius-models/migrations/create_operator_roles_table.stub';
        (new \CreateOperatorRolesTable())->up();

        require_once __DIR__ . '/../vendor/grixu/socius-models/migrations/create_operators_table.stub';
        (new \CreateOperatorsTable())->up();

        require_once __DIR__ . '/../vendor/grixu/socius-models/migrations/create_operator_branch_pivot_table.stub';
        (new \CreateOperatorBranchPivotTable())->up();

        $this->config = new SyncConfig(
            FakeLoader::class,
            FakeParser::class,
            Operator::class,
            'xlId',
            config('synchronizer.jobs.default')
        );
        $this->batchId = '11-111-111-11';

        $this->operatorRole = OperatorRole::factory()->create();
        $this->branch = Branch::factory()->create();
    }

    /** @test */
    public function it_creates_itself()
    {
        $this->data = [
            $this->makeBelongsToCase(),
            $this->makeBelongsToManyCase()
        ];

        $this->obj = new Synchronizer($this->data, $this->config, $this->batchId);

        $this->assertEquals(Synchronizer::class, get_class($this->obj));
    }

    protected function makeBelongsToCase(): array
    {
        return OperatorDataFactory::new()->create(
            [
                'relations' => [
                    [
                        'foreignClass' => OperatorRole::class,
                        'relation' => 'role',
                        'foreignField' => 'xl_id',
                        'type' => BelongsTo::class,
                        'foreignKeys' => (int)$this->operatorRole->xl_id,
                    ]
                ]
            ]
        )->toArray();
    }

    protected function makeBelongsToManyCase(): array
    {
        return OperatorDataFactory::new()->create(
            [
                'relations' => [
                    [
                        'foreignClass' => Branch::class,
                        'relation' => 'branches',
                        'foreignField' => 'xl_id',
                        'type' => BelongsToMany::class,
                        'foreignKeys' => [(int)$this->branch->xl_id],
                    ]
                ]
            ]
        )->toArray();
    }

    /** @test */
    public function it_sync_both_belongs_to_and_belongs_to_many()
    {
        $this->data = [
            $this->makeBelongsToCase(),
            $this->makeBelongsToManyCase()
        ];

        $this->obj = new Synchronizer($this->data, $this->config, $this->batchId);

        $this->assertDatabaseCount('operators', 0);

        $this->obj->sync();

        $this->assertDatabaseCount('operators', 2);

        foreach ($this->data as $data) {
            $model = Operator::where('xl_id', $data['xlId'])->first();

            if ($model) {
                $relation = $data['relations'][0]['relation'];
                $this->assertNotEmpty($model->$relation);
            } else {
                $this->assertTrue(false);
            }
        }
    }

    /** @test */
    public function it_saves_log()
    {
        $this->assertDatabaseCount('synchronizer_logs', 0);

        $this->it_sync_both_belongs_to_and_belongs_to_many();

        $this->assertDatabaseCount('synchronizer_logs', 3);
    }

    /** @test */
    public function it_emits_event()
    {
        Event::fake();

        $this->it_sync_both_belongs_to_and_belongs_to_many();

        Event::assertDispatched(SynchronizerEvent::class);
    }

    /** @test */
    public function it_throws_exception_on_empty_data()
    {
        $this->data = [];

        try {
            $this->obj = new Synchronizer($this->data, $this->config, $this->batchId);
            $this->assertTrue(false);
        } catch (\Exception) {
            $this->assertTrue(true);
        }
    }
}
