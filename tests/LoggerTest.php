<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\Synchronizer\Models\Log;
use Grixu\Synchronizer\Logger;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Model;

class LoggerTest extends TestCase
{
    protected function dbLoggingOff($app)
    {
        $app['config']->set('synchronizer.sync.logging', false);
    }

    /** @test */
    public function it_creates()
    {
        $obj = new Logger(Model::class, 1);
        $this->basicAssertions($obj);
        $this->assertEmpty($obj->get());
    }

    public function basicAssertions($obj): void
    {
        $this->assertNotEmpty($obj->getId());
        $this->assertEquals(1, $obj->getId());
        $this->assertNotEmpty($obj->getModel());
        $this->assertEquals(Model::class, $obj->getModel());
        $this->assertIsArray($obj->get());
    }

    /** @test */
    public function check_results()
    {
        $obj = new Logger(Model::class, 1);
        $obj->addChanges('name', 'named', 'Lol', 'Rotfl');
        $results = $obj->get();

        $this->basicAssertions($obj);

        $this->assertEquals('name', $results[0]['dtoField']);
        $this->assertEquals('Lol', $results[0]['dtoValue']);
        $this->assertEquals('named', $results[0]['modelField']);
        $this->assertEquals('Rotfl', $results[0]['modelValue']);
    }

    /** @test */
    public function check_saving()
    {
        $this->assertDatabaseCount('synchronizer_logs', 0);

        $obj = new Logger(Model::class, 1);
        $obj->addChanges('name', 'named', 'Lol', 'Rotfl');
        $obj->save();

        $obj = Log::query()->first();
        $this->assertDatabaseCount('synchronizer_logs', 1);
        $this->assertNotEmpty($obj);
        $this->assertNotEmpty($obj->model);
        $this->assertNotEmpty($obj->model_id);
        $this->assertNotEmpty($obj->log);
        $this->assertIsArray($obj->log);
        $this->assertCount(1, $obj->log);
        $this->assertEquals(Model::class, $obj->model);
        $this->assertEquals(1, $obj->model_id);
        $this->assertEquals('name', $obj->log[0]['dtoField']);
        $this->assertEquals('named', $obj->log[0]['modelField']);
        $this->assertEquals('Lol', $obj->log[0]['dtoValue']);
        $this->assertEquals('Rotfl', $obj->log[0]['modelValue']);
    }

    /** @test */
    public function check_results_when_not_changed_data_was_added()
    {
        $this->assertDatabaseCount('synchronizer_logs', 0);

        $obj = new Logger(Model::class, 1);
        $obj->addChanges('name', 'named', 'Lol', 'Lol');

        $results = $obj->get();
        $this->assertEmpty($results);

        $obj->save();
        $this->assertDatabaseCount('synchronizer_logs', 0);
    }

    /** @test */
    public function check_add_null_values()
    {
        $obj = new Logger(Model::class, 1);

        $obj->addChanges('name', 'named', null, 'Rotfl');

        $results = $obj->get();

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertEquals('name', $results[0]['dtoField']);
        $this->assertEmpty($results[0]['dtoValue']);
        $this->assertEquals('named', $results[0]['modelField']);
        $this->assertEquals('Rotfl', $results[0]['modelValue']);
    }

    /**
     * @test
     * @environment-setup timestampConfig
     */
    public function check_excluded_timestamps_are_not_logged()
    {
        $this->assertDatabaseCount('synchronizer_logs', 0);

        $obj = new Logger(Model::class, 1);
        $obj->addChanges('updatedAt', 'updated_at', 'some', 'Rotfl');
        $this->assertEmpty($obj->get());

        $obj->save();
        $this->assertDatabaseCount('synchronizer_logs', 0);
    }

    /**
     * @test
     * @environment-setup dbLoggingOff
     */
    public function when_sync_logging_is_off()
    {
        $this->assertDatabaseCount('synchronizer_logs', 0);

        $obj = new Logger(Model::class, 1);
        $obj->addChanges('name', 'named', 'Lol', 'Rotfl');
        $obj->save();

        $this->assertDatabaseCount('synchronizer_logs', 0);
    }
}
