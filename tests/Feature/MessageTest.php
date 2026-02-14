<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\DeliveryLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_crear_un_mensaje(): void
    {
        $message = Message::create([
            'title' => 'Test titulo',
            'content' => 'Test contenido del mensaje',
        ]);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'title' => 'Test titulo',
            'status' => 'pending',
        ]);
    }

    public function test_mensaje_tiene_relacion_con_delivery_logs(): void
    {
        $message = Message::create([
            'title' => 'Test relacion',
            'content' => 'Contenido de prueba',
        ]);

        DeliveryLog::create([
            'message_id' => $message->id,
            'channel' => 'email',
            'status' => 'sent',
            'payload' => ['title' => 'Test relacion', 'summary' => 'Resumen'],
        ]);

        DeliveryLog::create([
            'message_id' => $message->id,
            'channel' => 'slack',
            'status' => 'failed',
            'error_message' => 'Timeout',
        ]);

        $this->assertCount(2, $message->deliveryLogs);
        $this->assertEquals('email', $message->deliveryLogs[0]->channel);
        $this->assertEquals('sent', $message->deliveryLogs[0]->status);
        $this->assertEquals('failed', $message->deliveryLogs[1]->status);
    }

    public function test_delivery_log_pertenece_a_mensaje(): void
    {
        $message = Message::create([
            'title' => 'Test belongsTo',
            'content' => 'Contenido',
        ]);

        $log = DeliveryLog::create([
            'message_id' => $message->id,
            'channel' => 'sms',
            'status' => 'sent',
        ]);

        $this->assertEquals($message->id, $log->message->id);
        $this->assertEquals('Test belongsTo', $log->message->title);
    }

    public function test_delivery_log_payload_se_castea_a_array(): void
    {
        $message = Message::create([
            'title' => 'Test cast',
            'content' => 'Contenido',
        ]);

        $log = DeliveryLog::create([
            'message_id' => $message->id,
            'channel' => 'email',
            'status' => 'sent',
            'payload' => ['title' => 'Test', 'summary' => 'Resumen'],
        ]);

        $log->refresh();
        $this->assertIsArray($log->payload);
        $this->assertEquals('Resumen', $log->payload['summary']);
    }
}
