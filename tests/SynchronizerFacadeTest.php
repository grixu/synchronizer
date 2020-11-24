<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\Synchronizer\Synchronizer;
use Grixu\Synchronizer\SynchronizerFacade;
use Grixu\Synchronizer\Tests\Helpers\BaseTestCase;
use Grixu\Synchronizer\Tests\Helpers\ProductDataFactory;
use Grixu\Synchronizer\Tests\Helpers\ProductFactory;

/**
 * Class SynchronizerFacadeTest
 * @package Grixu\Synchronizer\Tests
 */
class SynchronizerFacadeTest extends BaseTestCase
{
    /** @test */
    public function check_facade()
    {
        $map = [
            'name' => 'name',
            'index' => 'index',
            'ean' => 'ean',
            'weight' => 'weight',
        ];

        $local = ProductFactory::new()->make();
        $local->id = 1;
        $foreign = ProductDataFactory::new()->create();

        $obj = SynchronizerFacade::make($map, $local, $foreign);

        $this->assertEquals(Synchronizer::class, get_class($obj));
    }
}
