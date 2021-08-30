<?php

namespace Grixu\Synchronizer\Tests\Engine\Config;

use Grixu\Synchronizer\Engine\Config\NullEngineConfig;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class NullEngineConfigTest extends TestCase
{
    /** @test */
    public function it_constructs_with_no_param()
    {
        $obj = new NullEngineConfig();

        $this->assertNotEmpty($obj);
        $this->assertEquals(NullEngineConfig::class, $obj::class);
    }

    /** @test */
    public function it_provide_empty_results()
    {
        $obj = new NullEngineConfig();

        $this->assertEmpty($obj->getModel());
        $this->assertEmpty($obj->getKey());
        $this->assertEmpty($obj->getExcluded());
        $this->assertEmpty($obj->getFillable());
        $this->assertEmpty($obj->getOnly());
        $this->assertEmpty($obj->isOnlyMode());
        $this->assertEmpty($obj->getChecksumField());
        $this->assertEmpty($obj->getTimestamps());
        $this->assertEmpty($obj->getIds());
    }
}
