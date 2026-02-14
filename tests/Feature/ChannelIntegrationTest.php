<?php

namespace Tests\Feature;

use App\Services\Channels\EmailChannel;
use App\Services\Channels\SmsChannel;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ChannelIntegrationTest extends TestCase
{
    public function test_email_channel_envia_y_loguea_payload(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, '[Email Channel]')
                    && $context['title'] === 'Test Email'
                    && $context['summary'] === 'Resumen test';
            });

        $channel = new EmailChannel();
        $result = $channel->send('Test Email', 'Resumen test', 'Contenido completo');

        $this->assertTrue($result);
    }

    public function test_sms_channel_genera_xml_soap_correcto(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, '[SMS Channel]')
                    && str_contains($context['xml'], 'soapenv:Envelope')
                    && str_contains($context['xml'], '<sms:message>Resumen SMS</sms:message>')
                    && str_contains($context['xml'], '<sms:reference>Test SMS</sms:reference>')
                    && str_contains($context['xml'], '<sms:destination>+570000000000</sms:destination>');
            });

        $channel = new SmsChannel();
        $result = $channel->send('Test SMS', 'Resumen SMS', 'Contenido original');

        $this->assertTrue($result);
    }
}
