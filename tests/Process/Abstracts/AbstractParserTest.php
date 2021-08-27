<?php

namespace Grixu\Synchronizer\Tests\Process\Abstracts;

use Grixu\Synchronizer\Config\EngineConfig;
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
        Config::set('synchronizer.checksum.timestamps_excluded', true);
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
    public function it_excluding_field_from_dto()
    {
        EngineConfig::setInstance(
            FakeEngineConfig::make(excludedFields: ['name'])
        );

        $result = $this->obj->parse($this->data);

        $this->assertNotEmpty($result);
        $result->each(fn ($item) => $this->assertArrayNotHasKey('name', $item));
    }
}
