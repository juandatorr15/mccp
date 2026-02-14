<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiSummaryService
{
    public function generateSummary(string $content): string
    {
        $provider = config('services.ai.provider', 'openai');

        return match ($provider) {
            'openai' => $this->callOpenAi($content),
            'claude' => $this->callClaude($content),
            'gemini' => $this->callGemini($content),
            default => throw new \RuntimeException("Proveedor de IA no soportado: {$provider}"),
        };
    }

    private function callOpenAi(string $content): string
    {
        $apiKey = config('services.ai.openai_key');

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Genera un resumen ejecutivo del siguiente contenido en máximo 100 caracteres. Responde SOLO con el resumen, sin comillas ni explicaciones adicionales.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $content,
                    ],
                ],
                'max_tokens' => 60,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Error al llamar a OpenAI: ' . $response->body());
        }

        $summary = $response->json('choices.0.message.content');

        return mb_substr(trim($summary), 0, 100);
    }

    private function callClaude(string $content): string
    {
        $apiKey = config('services.ai.claude_key');

        $response = Http::timeout(30)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-5-20250929',
                'max_tokens' => 60,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Genera un resumen ejecutivo del siguiente contenido en máximo 100 caracteres. Responde SOLO con el resumen, sin comillas ni explicaciones adicionales.\n\n{$content}",
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Error al llamar a Claude: ' . $response->body());
        }

        $summary = $response->json('content.0.text');

        return mb_substr(trim($summary), 0, 100);
    }

    private function callGemini(string $content): string
    {
        $apiKey = config('services.ai.gemini_key');

        $response = Http::timeout(30)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "Genera un resumen ejecutivo del siguiente contenido en máximo 100 caracteres. Responde SOLO con el resumen, sin comillas ni explicaciones adicionales.\n\n{$content}",
                            ],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Error al llamar a Gemini: ' . $response->body());
        }

        $summary = $response->json('candidates.0.content.parts.0.text');

        return mb_substr(trim($summary), 0, 100);
    }
}
