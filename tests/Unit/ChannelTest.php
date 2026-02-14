<?php

namespace Tests\Unit;

use App\Services\Channels\EmailChannel;
use App\Services\Channels\SlackChannel;
use App\Services\Channels\SmsChannel;
use PHPUnit\Framework\TestCase;

class ChannelTest extends TestCase
{
    public function test_email_channel_retorna_nombre_correcto(): void
    {
        $channel = new EmailChannel();
        $this->assertEquals('email', $channel->getName());
    }

    public function test_slack_channel_retorna_nombre_correcto(): void
    {
        $channel = new SlackChannel();
        $this->assertEquals('slack', $channel->getName());
    }

    public function test_sms_channel_retorna_nombre_correcto(): void
    {
        $channel = new SmsChannel();
        $this->assertEquals('sms', $channel->getName());
    }
}
