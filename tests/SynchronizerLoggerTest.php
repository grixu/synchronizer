<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\Synchronizer\DataTransferObjects\SynchronizerLogData;
use Grixu\Synchronizer\DataTransferObjects\SynchronizerLogEntryCollection;
use Grixu\Synchronizer\Models\SynchronizerLog;
use Grixu\Synchronizer\SynchronizerLogger;
use Grixu\Synchronizer\Tests\Helpers\BaseTestCase;

/**
 * Class SynchronizerLoggerTest
 * @package Grixu\Synchronizer\Tests
 */
class SynchronizerLoggerTest extends BaseTestCase
{
    /** @test */
    public function check_constructor()
    {
        $obj = new SynchronizerLogger('Product', 1);

        $this->assertNotEmpty($obj->getId());
        $this->assertNotEmpty($obj->getModel());
        $this->assertEmpty($obj->getChanges());
        $this->assertNotEmpty($obj->get());
        $this->assertEquals(SynchronizerLogData::class, get_class($obj->get()));
    }

    /** @test */
    public function check_results()
    {
        $obj = new SynchronizerLogger('Product', 1);

        $obj->addChanges('name', 'named', 'Lol', 'Rotfl');

        $results = $obj->get();

        $this->assertEquals(SynchronizerLogData::class, get_class($results));
        $this->assertEquals(SynchronizerLogEntryCollection::class, get_class($results->changes));
        $this->assertNotEmpty($results->changes);
        $this->assertEquals('name', $results->changes->current()->localField);
        $this->assertEquals('Lol', $results->changes->current()->localValue);
        $this->assertEquals('named', $results->changes->current()->foreignField);
        $this->assertEquals('Rotfl', $results->changes->current()->foreignValue);
    }

    /** @test */
    public function check_saving()
    {
        SynchronizerLog::query()->delete();

        $this->assertDatabaseCount('synchronizer_logs', 0);

        $obj = new SynchronizerLogger('Product', 1);
        $obj->addChanges('name', 'named', 'Lol', 'Rotfl');
        $obj->save();

        $obj = SynchronizerLog::query()->first();
        $this->assertDatabaseCount('synchronizer_logs', 1);
        $this->assertNotEmpty($obj);
        $this->assertNotEmpty($obj->model);
        $this->assertNotEmpty($obj->model_id);
        $this->assertNotEmpty($obj->log);
        $this->assertIsArray($obj->log);
        $this->assertCount(1, $obj->log);
        $this->assertEquals('Product', $obj->model);
        $this->assertEquals(1, $obj->model_id);
        $this->assertEquals('name', $obj->log[0]['localField']);
        $this->assertEquals('named', $obj->log[0]['foreignField']);
        $this->assertEquals('Lol', $obj->log[0]['localValue']);
        $this->assertEquals('Rotfl', $obj->log[0]['foreignValue']);
    }

    /** @test */
    public function check_results_when_not_changed_data_was_added()
    {
        SynchronizerLog::query()->delete();

        $this->assertDatabaseCount('synchronizer_logs', 0);

        $obj = new SynchronizerLogger('Product', 1);

        $obj->addChanges('name', 'named', 'Lol', 'Lol');

        $results = $obj->get();

        $this->assertEquals(SynchronizerLogData::class, get_class($results));
        $this->assertEquals(SynchronizerLogEntryCollection::class, get_class($results->changes));
        $this->assertEmpty($results->changes);

        $obj->save();

        $this->assertDatabaseCount('synchronizer_logs', 0);
    }

    /** @test */
    public function check_add_null_values()
    {
        $obj = new SynchronizerLogger('Product', 1);

        $obj->addChanges('name', 'named', null, 'Rotfl');

        $results = $obj->get();

        $this->assertEquals(SynchronizerLogData::class, get_class($results));
        $this->assertEquals(SynchronizerLogEntryCollection::class, get_class($results->changes));
        $this->assertNotEmpty($results->changes);
        $this->assertEquals('name', $results->changes->current()->localField);
        $this->assertEmpty($results->changes->current()->localValue);
        $this->assertEquals('named', $results->changes->current()->foreignField);
        $this->assertEquals('Rotfl', $results->changes->current()->foreignValue);
    }

    /** @test */
    public function check_excluded_from_logging_fields()
    {
        SynchronizerLog::query()->delete();

        $this->assertDatabaseCount('synchronizer_logs', 0);

        $obj = new SynchronizerLogger('Product', 1);

        $obj->addChanges('updated_at', 'named', 'some', 'Rotfl');

        $results = $obj->get();

        $this->assertEquals(SynchronizerLogData::class, get_class($results));
        $this->assertEquals(SynchronizerLogEntryCollection::class, get_class($results->changes));
        $this->assertEmpty($results->changes);

        $obj->save();

        $this->assertDatabaseCount('synchronizer_logs', 0);
    }
}
