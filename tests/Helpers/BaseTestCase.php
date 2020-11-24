<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use CreateSynchronizerFieldsTable;
use CreateSynchronizerLogsTable;
use Grixu\Synchronizer\SynchronizerServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class TestCase
 * @package Grixu\SyncLog\Tests\Helpers
 */
class BaseTestCase extends TestCase
{
    protected array $map;
    protected Model $model;
    protected DataTransferObject $data;

    protected function getPackageProviders($app)
    {
        return [SynchronizerServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        include_once __DIR__.'/../../database/migrations/2020_11_20_133728_create_synchronizer_fields_table.php';
        include_once __DIR__.'/../../database/migrations/2020_11_20_133728_create_synchronizer_logs_table.php';

        (new CreateSynchronizerFieldsTable())->up();
        (new CreateSynchronizerLogsTable())->up();
    }
}
