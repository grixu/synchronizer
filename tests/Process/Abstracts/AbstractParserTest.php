<?php

namespace Grixu\Synchronizer\Tests\Process\Abstracts;

use Grixu\Synchronizer\Engine\Config\EngineConfig;
use Grixu\Synchronizer\Tests\Helpers\FakeEngineConfig;
use Grixu\Synchronizer\Tests\Helpers\FakeForeignSqlSourceModel;
use Grixu\Synchronizer\Tests\Helpers\FakeParser;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class AbstractParserTest extends SyncTestCase
{
    protected FakeParser $obj;
    protected Collection $data;

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = FakeForeignSqlSourceModel::limit(10)->get();
        $this->obj = new FakeParser();
    }

    /** @test */
    public function it_creating_checksums()
    {
        $result = $this->obj->parse($this->data);

        $this->assertNotEmpty($result);
        $this->assertCount(10, $result);
        $result->each(fn ($item) => $this->assertNotEmpty($item[config('synchronizer.checksum.field')]));
    }

    /** @test */
    public function it_can_excluding_timestamps()
    {
        EngineConfig::setInstance(
            FakeEngineConfig::make(timestamps: ['Knt_SyncTimeStamp'], checksumField: 'checksum')
        );

        $takeOne = $this->obj->parse($this->data);

        $this->data = $this->data->map(function ($item) {
            $item->Knt_SyncTimeStamp = now();
            return $item;
        });

        $takeTwo = $this->obj->parse($this->data);

        $this->assertEquals($takeOne, $takeTwo);
    }

    /** @test */
    public function it_excluding_fields()
    {
        EngineConfig::setInstance(
            FakeEngineConfig::make(fields: ['name'])
        );

        $test = $this->obj->parse($this->data);
        $this->assertNotEmpty($test);
        $test->each(fn ($item) => $this->assertArrayNotHasKey('name', $item));
    }

    /** @test */
    public function it_gathering_fillable_fields()
    {
        EngineConfig::setInstance(
            FakeEngineConfig::make(fields: ['name'=>['fillable']])
        );

        $test = $this->obj->parse($this->data);
        $this->assertNotEmpty($test);
        $test->each(function ($item) {
            $this->assertArrayNotHasKey('name', $item);
            $this->assertArrayHasKey('name', $item['fillable']);
        });
    }

    /** @test */
    public function it_strip_all_fields_in_only_mode()
    {
        EngineConfig::setInstance(
            FakeEngineConfig::make(fields: ['name', 'country'], mode: EngineConfig::ONLY)
        );

        $test = $this->obj->parse($this->data);
        $this->assertNotEmpty($test);
        $test->each(function ($item) {
            $this->assertArrayHasKey('name', $item);
            $this->assertArrayHasKey('country', $item);
            $this->assertArrayNotHasKey('fillable', $item);
            // checksum & key field - that's why +2
            $this->assertCount(4, $item);
        });
    }
}
