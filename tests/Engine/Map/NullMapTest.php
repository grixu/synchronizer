<?php

namespace Grixu\Synchronizer\Tests\Engine\Map;

use Grixu\Synchronizer\Engine\Contracts\Map;
use Grixu\Synchronizer\Engine\Map\NullMap;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Throwable;

class NullMapTest extends TestCase
{
    /** @test */
    public function it_creates_obj_which_returns_any_data()
    {
        $obj = NullMap::make();

        $this->assertTrue($obj instanceof Map);

        $this->assertIsArray($obj->get());
        $this->assertIsArray($obj->getModelFieldsArray());
        $this->assertIsArray($obj->getUpdatableOnNullFields());
        $this->assertIsArray($obj->getWithoutTimestamps());

        try {
            $obj->add('field', 'field');
        } catch (Throwable) {
            $this->assertTrue(false);
        }
    }
}
