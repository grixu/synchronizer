<?php

namespace Grixu\Synchronizer\Tests\Logs\Notifications;

use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Logs\Notifications\LoggerNotification;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Notifications\Messages\SlackMessage;

class LoggerNotificationTest extends TestCase
{
    /** @test */
    public function it_produces_slack_message()
    {
        $obj = new LoggerNotification(Product::class, 0);
        $message = $obj->toSlack(null);

        $this->assertEquals(SlackMessage::class, $message::class);
    }
}
