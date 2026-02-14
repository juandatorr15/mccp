<?php

namespace App\Services\Channels;

use Illuminate\Support\Facades\Log;

class EmailChannel implements ChannelInterface
{
    public function send(string $title, string $summary, string $originalContent): bool
    {
        $payload = [
            'title' => $title,
            'summary' => $summary,
            'original_content' => $originalContent,
        ];

        Log::info('[Email Channel] Payload enviado:', $payload);

        return true;
    }

    public function getName(): string
    {
        return 'email';
    }
}
