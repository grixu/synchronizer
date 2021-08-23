<?php

namespace Grixu\Synchronizer\Tests\Config;

use Grixu\Synchronizer\Config\NullSyncConfig;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class NullSyncConfigTest extends TestCase
{
    /** @test */
    public function it_constructs_with_no_param()
    {
        $obj = new NullSyncConfig();

        $this->assertNotEmpty($obj);
        $this->assertEquals(NullSyncConfig::class, $obj::class);
    }

    /** @test */
    public function it_provide_empty_results()
    {
        $obj = new NullSyncConfig();

        $this->assertEmpty($obj->getChecksumField());
        $this->assertEmpty($obj->getForeignKey());
        $this->assertEmpty($obj->getTimestamps());
        $this->assertEmpty($obj->getLocalModel());
        $this->assertEmpty($obj->getIds());
        $this->assertEmpty($obj->getCurrentJob());
        $this->assertEmpty($obj->getLoaderClass());
        $this->assertEmpty($obj->getParserClass());
        $this->assertEmpty($obj->getNextJob());
        $this->assertEmpty($obj->getSyncClosure()());
        $this->assertEmpty($obj->getErrorHandler()());

        try {
            $obj->setCurrentJob(0);
            $obj->setErrorHandler(function () {});
            $obj->setSyncClosure(function () {});

            $this->assertTrue(true);
        } catch (\Throwable) {
            $this->fail();
        }
    }
}
