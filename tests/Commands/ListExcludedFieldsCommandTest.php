<?php

namespace Grixu\Synchronizer\Tests\Commands;

use Grixu\Synchronizer\Tests\Helpers\BaseTestCase;
use Grixu\Synchronizer\Models\SynchronizerField;
use Grixu\Synchronizer\Tests\Helpers\SynchronizerFieldFactory;

/**
 * Class ListExcludedFieldsCommandTest
 * @package Grixu\Synchronizer\Tests
 */
class ListExcludedFieldsCommandTest extends BaseTestCase
{
    /** @test */
    public function walkthrough()
    {
        $model = SynchronizerFieldFactory::new()->create();

        $this->artisan('synchronizer:list')
            ->expectsOutput('List of Excluded fields:')
            ->expectsQuestion('Would like to take some action like as:', 'delete')
            ->expectsQuestion('Choose model', $model->model)
            ->expectsQuestion('Choose field', $model->field)
            ->expectsOutput('DELETED')
            ->expectsQuestion('Would like to take some action like as:', 'exit')
            ->assertExitCode(0);
    }

    /** @test */
    public function check_with_no_data()
    {
        SynchronizerField::query()->delete();

        $this->artisan('synchronizer:list')
            ->expectsOutput('List of Excluded fields:')
            ->expectsOutput('Nothing found. Exiting.')
            ->assertExitCode(0);
    }
}
