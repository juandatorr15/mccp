<?php

namespace App\Services;

use App\Models\DeliveryLog;
use App\Models\Message;
use App\Services\Channels\ChannelInterface;
use App\Services\Channels\EmailChannel;
use App\Services\Channels\SlackChannel;
use App\Services\Channels\SmsChannel;
use Illuminate\Support\Facades\Log;

class MessageProcessorService
{
    private array $channelMap;

    public function __construct(
        private AiSummaryService $aiService,
    ) {
        $this->channelMap = [
            'email' => new EmailChannel(),
            'slack' => new SlackChannel(),
            'sms' => new SmsChannel(),
        ];
    }

    public function process(Message $message, array $channels): Message
    {
        $message->update(['status' => 'processing']);

        // Paso 1: Generar resumen con IA (si falla, no se envía nada)
        try {
            $summary = $this->aiService->generateSummary($message->content);
            $message->update(['ai_summary' => $summary]);
        } catch (\Throwable $e) {
            Log::error('[MCCP] Error al generar resumen IA: ' . $e->getMessage());
            $message->update(['status' => 'failed']);

            // Crear logs de fallo para todos los canales
            foreach ($channels as $channel) {
                DeliveryLog::create([
                    'message_id' => $message->id,
                    'channel' => $channel,
                    'status' => 'failed',
                    'error_message' => 'Fallo en generación de resumen IA: ' . $e->getMessage(),
                ]);
            }

            return $message->fresh(['deliveryLogs']);
        }

        // Paso 2: Distribuir a cada canal (independientemente)
        foreach ($channels as $channelName) {
            $this->sendToChannel($message, $channelName, $summary);
        }

        // Determinar estado final
        $allLogs = $message->deliveryLogs()->get();
        $allFailed = $allLogs->every(fn ($log) => $log->status === 'failed');
        $message->update(['status' => $allFailed ? 'failed' : 'completed']);

        return $message->fresh(['deliveryLogs']);
    }

    private function sendToChannel(Message $message, string $channelName, string $summary): void
    {
        $channel = $this->channelMap[$channelName] ?? null;

        if (!$channel) {
            DeliveryLog::create([
                'message_id' => $message->id,
                'channel' => $channelName,
                'status' => 'failed',
                'error_message' => "Canal '{$channelName}' no soportado.",
            ]);
            return;
        }

        $payload = [
            'title' => $message->title,
            'summary' => $summary,
            'original_content' => $message->content,
        ];

        try {
            $channel->send($message->title, $summary, $message->content);

            DeliveryLog::create([
                'message_id' => $message->id,
                'channel' => $channelName,
                'status' => 'sent',
                'payload' => $payload,
            ]);
        } catch (\Throwable $e) {
            Log::error("[MCCP] Error en canal {$channelName}: " . $e->getMessage());

            DeliveryLog::create([
                'message_id' => $message->id,
                'channel' => $channelName,
                'status' => 'failed',
                'payload' => $payload,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
