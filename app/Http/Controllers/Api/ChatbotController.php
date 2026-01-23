<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotService $chatbotService
    ) {}

    /**
     * Send a message to the chatbot
     *
     * @unauthenticated
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'message' => 'required|string|max:1000',
            'client_id' => 'nullable|integer|exists:clients,id',
        ]);

        $response = $this->chatbotService->processMessage(
            sessionId: $request->session_id,
            message: $request->message,
            clientId: $request->client_id
        );

        return response()->json($response);
    }

    /**
     * Get chat history for a session
     *
     * @unauthenticated
     */
    public function getHistory(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $history = $this->chatbotService->getHistory($request->session_id);

        return response()->json(['history' => $history]);
    }

    /**
     * Start a new chat session
     *
     * @unauthenticated
     */
    public function startSession(): JsonResponse
    {
        $sessionId = uniqid('chat_', true);

        return response()->json([
            'session_id' => $sessionId,
            'message' => "Hello! 👋 Welcome to Skyydo Insurance. I'm here to help you with your insurance needs. How can I assist you today?",
        ]);
    }
}
