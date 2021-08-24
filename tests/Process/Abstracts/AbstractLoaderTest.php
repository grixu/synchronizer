<?php

namespace Grixu\Synchronizer\Tests\Process\Abstracts;

use Grixu\Synchronizer\Tests\Helpers\FakeLoader;
use Grixu\Synchronizer\Tests\Helpers\SyncTestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AbstractLoaderTest extends SyncTestCase
{
    protected FakeLoader $obj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->obj = new FakeLoader();
    }

    /** @test */
    public function it_returns_count_of_objects()
    {
        $returnedData = $this->obj->getCount();

        $this->assertNotEmpty($returnedData);
        $this->assertGreaterThan(0, $returnedData);
    }

    /** @test */
    public function it_returns_split_collection()
    {
        $returnedData = $this->obj->get();

        $this->assertNotEmpty($returnedData);
        $this->assertTrue($returnedData instanceof Collection);

        $count = ceil($this->obj->getCount() / config('synchronizer.sync.default_chunk_size'));
        $this->assertCount($count, $returnedData);
    }

    /** @test */
    public function it_returns_raw_collection()
    {
        $returnedData = $this->obj->getRaw();

        $this->assertNotEmpty($returnedData);
        $this->assertTrue($returnedData instanceof Collection);
        $this->assertCount($this->obj->getCount(), $returnedData);
    }

    /** @test */
    public function it_returns_piece()
    {
        $returnedData = $this->obj->getPiece(2);

        $this->assertNotEmpty($returnedData);
        $this->assertTrue($returnedData instanceof Collection);
        $this->assertCount(config('synchronizer.sync.default_chunk_size'), $returnedData);
    }

    /** @test */
    public function it_returns_builder_obj()
    {
        $returnedData = $this->obj->getBuilder();

        $this->assertNotEmpty($returnedData);
        $this->assertEquals(Builder::class, $returnedData::class);
    }
}
