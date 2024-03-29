<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\SociusModels\Customer\Models\Customer;
use Grixu\SociusModels\Operator\Factories\OperatorDataFactory;
use Grixu\SociusModels\Operator\Models\Branch;
use Grixu\SociusModels\Operator\Models\Operator;
use Grixu\SociusModels\Operator\Models\OperatorRole;
use Grixu\Synchronizer\Engine\Config\EngineConfig;
use Grixu\Synchronizer\Engine\Config\EngineConfigFactory;
use Grixu\Synchronizer\Engine\Contracts\EngineConfigInterface;
use Grixu\Synchronizer\Engine\Events\SynchronizerEvent;
use Grixu\Synchronizer\Synchronizer;
use Grixu\Synchronizer\Tests\Helpers\FakeEngineConfig;
use Grixu\Synchronizer\Tests\Helpers\FakeForeignSqlSourceModel;
use Grixu\Synchronizer\Tests\Helpers\FakeParser;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

class SynchronizerTest extends SyncTestCase
{
    use RefreshDatabase;

    protected array $data = [];
    protected EngineConfigInterface $config;
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

        $this->config = EngineConfigFactory::make(
            model: Operator::class,
            key: 'xlId',
            checksumField: config('synchronizer.checksum.field')
        );
        EngineConfig::setInstance($this->config);
        $this->batchId = '11-111-111-11';

        $this->operatorRole = OperatorRole::factory()->create();
        $this->branch = Branch::factory()->create();
    }

    /** @test */
    public function it_creates_itself()
    {
        $this->data = [
            $this->makeBelongsToCase(),
            $this->makeBelongsToManyCase(),
        ];

        $this->obj = new Synchronizer($this->data, $this->batchId);

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
                    ],
                ],
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
                    ],
                ],
            ]
        )->toArray();
    }

    /** @test */
    public function it_sync_both_belongs_to_and_belongs_to_many()
    {
        $this->data = [
            $this->makeBelongsToCase(),
            $this->makeBelongsToManyCase(),
        ];

        $this->obj = new Synchronizer($this->data, $this->batchId);

        $this->assertDatabaseCount('operators', 0);

        $this->obj->sync();

        $this->assertDatabaseCount('operators', 2);

        foreach ($this->data as $data) {
            $model = Operator::where('xl_id', $data['xlId'])->first();

            if ($model) {
                $relation = $data['relations'][0]['relation'];
                $this->assertNotEmpty($model->{$relation});
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
            $this->obj = new Synchronizer($this->data, $this->batchId);
            $this->assertTrue(false);
        } catch (\Exception) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_synchronize_excluded_fields()
    {
        EngineConfig::setInstance(FakeEngineConfig::make(fields: ['fillable'=>['country']]));

        $this->input = FakeForeignSqlSourceModel::limit(10)->get();
        $parser = new FakeParser();
        $this->data = $parser->parse($this->input)->toArray();
        $this->obj = new Synchronizer($this->data, $this->batchId);

        $this->assertDatabaseCount('customers', 0);

        $this->obj->sync();

        $this->assertDatabaseCount('customers', 10);
        $customer = Customer::query()->where('xl_id', $this->data[0]['xlId'])->first();

        $this->assertNotEmpty($customer);
        $this->assertNotEmpty($customer->country);
    }

    /** @test */
    public function it_not_even_start_sync_when_nothing_changed()
    {
        Config::set('synchronizer.sync.timestamps', ['updated_at']);

        $this->data = [
            $this->makeBelongsToCase(),
        ];
        $this->data[0]['checksum'] = 'aaa';

        $this->obj = new Synchronizer($this->data, $this->batchId);
        $this->obj->sync();

        $takeOne = Operator::where('xl_id', $this->data[0]['xlId'])->first();
        $this->assertNotEmpty($takeOne);

        $this->data[0]['updatedAt'] = now()->addSeconds(10);
        $this->obj = new Synchronizer($this->data, $this->batchId);
        $this->obj->sync();

        $takeTwo = Operator::where('xl_id', $this->data[0]['xlId'])->first();
        $this->assertNotEmpty($takeTwo);

        $this->assertEquals($takeOne->checksum, $takeTwo->checksum);
    }

    /**
     * @test
     * @environment-setup useDisabledChecksum
     */
    public function it_sync_with_checksum_control_off()
    {
        $this->it_sync_both_belongs_to_and_belongs_to_many();
    }

    protected function useDisabledChecksum($app)
    {
        $app->config->set('synchronizer.checksum.control', false);
    }
}
