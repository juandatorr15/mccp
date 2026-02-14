<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\DeliveryLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_get_messages_retorna_lista_vacia(): void
    {
        $response = $this->getJson('/api/messages');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    public function test_api_get_messages_retorna_historial_con_logs(): void
    {
        $message = Message::create([
            'title' => 'Test historial',
            'content' => 'Contenido',
            'ai_summary' => 'Resumen de prueba',
            'status' => 'completed',
        ]);

        DeliveryLog::create([
            'message_id' => $message->id,
            'channel' => 'email',
            'status' => 'sent',
            'payload' => ['title' => 'Test historial'],
        ]);

        $response = $this->getJson('/api/messages');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'title' => 'Test historial',
                'ai_summary' => 'Resumen de prueba',
            ])
            ->assertJsonFragment([
                'channel' => 'email',
                'status' => 'sent',
            ]);
    }

    public function test_api_post_messages_valida_campos_requeridos(): void
    {
        $response = $this->postJson('/api/messages', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content', 'channels']);
    }

    public function test_api_post_messages_valida_canales_permitidos(): void
    {
        $response = $this->postJson('/api/messages', [
            'title' => 'Test',
            'content' => 'Contenido',
            'channels' => ['whatsapp'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['channels.0']);
    }

    public function test_api_post_messages_requiere_al_menos_un_canal(): void
    {
        $response = $this->postJson('/api/messages', [
            'title' => 'Test',
            'content' => 'Contenido',
            'channels' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['channels']);
    }
}
