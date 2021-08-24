<?php

namespace Grixu\Synchronizer\Tests\Logs;

use Grixu\Synchronizer\Logs\Models\Log;
use Grixu\Synchronizer\Logs\Logger;
use Grixu\Synchronizer\Logs\Notifications\LoggerNotification;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
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
                'name' => 'One',
            ],
            [
                'name' => 'Two',
            ],
        ];
    }

    /** @test */
    public function it_save_changes_to_db()
    {
        $this->assertDatabaseCount('synchronizer_logs', 0);

        $this->obj->log($this->changes, Logger::BELONGS_TO);

        $this->assertDatabaseCount('synchronizer_logs', 1);
    }

    /** @test */
    public function it_calculates_changes_on_save()
    {
        $this->obj->log($this->changes, Logger::MODEL);

        $log = Log::first();
        $this->assertEquals(2, $log->changed);
    }

    /** @test */
    public function it_not_creating_empty_logs()
    {
        $this->changes = [];

        $this->assertDatabaseCount('synchronizer_logs', 0);

        $this->obj->log($this->changes, Logger::BELONGS_TO_MANY);

        $this->assertDatabaseCount('synchronizer_logs', 0);
    }

    /** @test */
    public function it_send_notifications()
    {
        Notification::fake();

        $this->obj->log($this->changes, Logger::BELONGS_TO_MANY);
        $this->obj->report();

        Notification::assertTimesSent(1, LoggerNotification::class);
    }

    /** @test */
    public function it_not_sending_notification_if_nothing_changed()
    {
        Notification::fake();

        $this->obj->log([], Logger::BELONGS_TO_MANY);
        $this->obj->report();

        Notification::assertTimesSent(0, LoggerNotification::class);
    }

    /** @test */
    public function it_not_sending_notification_if_webhook_is_not_configured()
    {
        Notification::fake();
        Config::set('synchronizer.logger.notifications.slack', null);

        $this->obj->log(['some'], Logger::BELONGS_TO_MANY);
        $this->obj->report();

        Notification::assertTimesSent(0, LoggerNotification::class);
    }
}
