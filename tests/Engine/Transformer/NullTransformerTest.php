<?php

namespace Grixu\Synchronizer\Tests\Engine\Transformer;

use Grixu\Synchronizer\Engine\Contracts\Map;
use Grixu\Synchronizer\Engine\Transformer\NullTransformer;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class NullTransformerTest extends TestCase
{
    /** @test */
    public function it_creates_obj_which_returns_any_data()
    {
        $obj = NullTransformer::make();

        $this->assertTrue($obj->getMap() instanceof Map);
        $this->assertIsArray($obj->sync(['field'], ['field']));
    }
}
