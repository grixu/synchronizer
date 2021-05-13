<?php

namespace Grixu\Synchronizer\Tests\Abstracts;

use Grixu\Synchronizer\Checksum;
use Grixu\Synchronizer\Tests\Helpers\FakeForeignSqlSourceModel;
use Grixu\Synchronizer\Tests\Helpers\FakeParser;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Illuminate\Support\Collection;

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
        $checksumField = Checksum::$checksumField;
        $result->each(fn ($item) => $this->assertNotEmpty($item[$checksumField]));
    }
}
