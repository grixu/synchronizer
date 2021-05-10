<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\Synchronizer\Models\Log;
use Grixu\Synchronizer\Logger;
use Grixu\Synchronizer\Notifications\LoggerNotification;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

class LoggerTest extends TestCase
{
    protected Logger $obj;
    protected array $changes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createObjAndChanges();
    }

    protected function createObjAndChanges()
    {
        $this->obj = new Logger('batch', Model::class);
        $this->changes = [
            [
                'name' => 'One'
            ],
            [
                'name' => 'Two'
            ]
        ];
    }

    /** @test */
    public function it_save_changes_to_db()
    {
        $this->assertDatabaseCount('synchronizer_logs', 0);

        $this->obj->log($this->changes);

        $this->assertDatabaseCount('synchronizer_logs', 1);
    }

    /** @test */
    public function it_calculates_changes_on_save()
    {
        $this->obj->log($this->changes);

        $log = Log::first();
        $this->assertEquals(2, $log->total_changes);
    }

    /** @test */
    public function it_not_creating_empty_logs()
    {
        $this->changes = [];

        $this->assertDatabaseCount('synchronizer_logs', 0);

        $this->obj->log($this->changes);

        $this->assertDatabaseCount('synchronizer_logs', 0);
    }

    /** @test */
    public function it_send_notifications()
    {
        Notification::fake();

        $this->obj->log($this->changes);
        $this->obj->report();

        Notification::assertTimesSent(1, LoggerNotification::class);
    }
}
