<?php

namespace App\Services\Channels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackChannel implements ChannelInterface
{
    public function send(string $title, string $summary, string $originalContent): bool
    {
        $webhookUrl = config('services.slack.webhook_url');

        $payload = [
            'title' => $title,
            'summary' => $summary,
            'original_content' => $originalContent,
        ];

        Log::info('[Slack Channel] Enviando payload a webhook:', $payload);

        $response = Http::timeout(10)->post($webhookUrl, $payload);

        if ($response->successful()) {
            Log::info('[Slack Channel] Enviado exitosamente');
            return true;
        }

        throw new \RuntimeException('Slack webhook respondió con status: ' . $response->status());
    }

    public function getName(): string
    {
        return 'slack';
    }
}
