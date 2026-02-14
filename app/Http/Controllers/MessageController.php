<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Services\MessageProcessorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private MessageProcessorService $processor,
    ) {}

    public function index(): JsonResponse
    {
        $messages = Message::with('deliveryLogs')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($messages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'channels' => 'required|array|min:1',
            'channels.*' => 'in:email,slack,sms',
        ]);

        $message = Message::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        $result = $this->processor->process($message, $validated['channels']);

        return response()->json($result, 201);
    }
}
