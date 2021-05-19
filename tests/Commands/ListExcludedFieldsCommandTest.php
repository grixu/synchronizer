<?php

namespace Grixu\Synchronizer\Tests\Commands;

use Grixu\Synchronizer\Engine\Models\ExcludedField;
use Grixu\Synchronizer\Tests\Helpers\TestCase;

class ListExcludedFieldsCommandTest extends TestCase
{
    /** @test */
    public function walkthrough()
    {
        $model = ExcludedField::factory()->create();

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
        ExcludedField::query()->delete();

        $this->artisan('synchronizer:list')
            ->expectsOutput('List of Excluded fields:')
            ->expectsOutput('Nothing found. Exiting.')
            ->assertExitCode(0);
    }
}
