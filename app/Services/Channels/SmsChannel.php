<?php

namespace App\Services\Channels;

use Illuminate\Support\Facades\Log;

class SmsChannel implements ChannelInterface
{
    public function send(string $title, string $summary, string $originalContent): bool
    {
        $xml = $this->buildSoapXml($title, $summary);

        Log::info('[SMS Channel] XML SOAP generado:', ['xml' => $xml]);

        return true;
    }

    public function getName(): string
    {
        return 'sms';
    }

    private function buildSoapXml(string $title, string $summary): string
    {
        $escapedSummary = htmlspecialchars($summary, ENT_XML1, 'UTF-8');
        $escapedTitle = htmlspecialchars($title, ENT_XML1, 'UTF-8');

        return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sms="http://ultracem.com/sms">
    <soapenv:Header/>
    <soapenv:Body>
        <sms:SendSmsRequest>
            <sms:destination>+570000000000</sms:destination>
            <sms:message>{$escapedSummary}</sms:message>
            <sms:reference>{$escapedTitle}</sms:reference>
        </sms:SendSmsRequest>
    </soapenv:Body>
</soapenv:Envelope>
XML;
    }
}
