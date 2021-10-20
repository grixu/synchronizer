<?php

namespace Grixu\Synchronizer\Tests\Engine;

use Grixu\SociusModels\Customer\Models\Customer;
use Grixu\Synchronizer\Engine\Config\EngineConfig;
use Grixu\Synchronizer\Engine\Contracts\Engine;
use Grixu\Synchronizer\Engine\ExcludedField;
use Grixu\Synchronizer\Engine\Map\MapFactory;
use Grixu\Synchronizer\Engine\Model;
use Grixu\Synchronizer\Engine\Transformer\Transformer;
use Grixu\Synchronizer\Tests\Helpers\FakeEngineConfig;
use Grixu\Synchronizer\Tests\Helpers\FakeForeignSqlSourceModel;
use Grixu\Synchronizer\Tests\Helpers\FakeParser;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

class ExcludedFieldTest extends SyncTestCase
{
    use RefreshDatabase;

    protected Engine $obj;
    protected Collection $data;
    protected Collection $input;
    protected Transformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        EngineConfig::setInstance(FakeEngineConfig::make(fields: ['fillable'=>['country']]));

        $this->input = FakeForeignSqlSourceModel::limit(10)->get();
        $parser = new FakeParser();
        $this->data = $parser->parse($this->input);

        $map = MapFactory::makeFromArray($this->data->first());
        $this->transformer = new Transformer($map);

        $model = new Model(EngineConfig::getInstance(), $this->data);
        $model->sync($this->transformer);

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
        $this->input->each(fn ($item) => $this->assertArrayNotHasKey('country', $item));

        $this->obj->sync($this->transformer);

        $checkData = Customer::all();
        $checkData->each(fn ($item) => $this->assertArrayHasKey('country', $item));
    }
}
