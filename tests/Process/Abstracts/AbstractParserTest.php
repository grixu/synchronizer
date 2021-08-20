<?php

namespace Grixu\Synchronizer\Tests\Process\Abstracts;

use Grixu\Synchronizer\Checksum;
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
        Config::set('synchronizer.sync.timestamps', ['Knt_SyncTimeStamp']);

        $takeOne = $this->obj->parse($this->data);

        $this->data = $this->data->map(function ($item) {
            $item->Knt_SyncTimeStamp = now();
            return $item;
        });

        $takeTwo = $this->obj->parse($this->data);

        $this->assertEquals($takeOne, $takeTwo);

    }
}
