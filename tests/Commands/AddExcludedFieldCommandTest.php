<?php

namespace Grixu\Synchronizer\Tests\Commands;

use Grixu\Synchronizer\Tests\Helpers\BaseTestCase;


/**
 * Class AddExcludedFieldCommandTest
 * @package Grixu\Synchronizer\Tests
 */
class AddExcludedFieldCommandTest extends BaseTestCase
{
    /** @test */
    public function walkthrough()
    {
        $this->artisan('synchronizer:add')
            ->expectsQuestion('Enter model name', 'Taylor')
            ->expectsQuestion('Now, enter field name', 'PHP')
            ->expectsQuestion('Update field when empty(null)?', 'yes')
            ->expectsOutput('Done!')
            ->assertExitCode(0);
    }
}
