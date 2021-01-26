<?php

namespace Grixu\Synchronizer\Tests\Commands;

use Grixu\Synchronizer\Tests\Helpers\TestCase;

class AddExcludedFieldCommandTest extends TestCase
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
