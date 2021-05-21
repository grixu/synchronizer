<?php

namespace Grixu\Synchronizer\Tests\Commands;

use Grixu\Synchronizer\Console\AddExcludedFieldCommand;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class AddExcludedFieldCommandTest extends TestCase
{
    /** @test */
    public function walkthrough()
    {
        $obj = new AddExcludedFieldCommand();
        $this->app->setBasePath(__DIR__.'/../../');

        $this->artisan('synchronizer:add')
            ->expectsChoice('Select model', '0', $obj->getModels())
            ->expectsQuestion('Now, enter field name', 'PHP')
            ->expectsQuestion('Update field when empty(null)?', 'yes')
            ->expectsOutput('Done!')
            ->assertExitCode(0);
    }
}
