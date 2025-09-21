<?php

namespace App\Http\Controllers;

use App\Models\ChatbotSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatbotController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $settings = ChatbotSetting::current();

        if (! $settings->module_enabled || blank($settings->endpoint)) {
            return response()->json([
                'message' => 'Chatbot belum diaktifkan.',
            ], 503);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:3000'],
            'history' => ['nullable', 'array'],
            'history.*.role' => ['required_with:history', 'string'],
            'history.*.content' => ['required_with:history', 'string'],
            'session' => ['nullable', 'string', 'max:120'],
            'metadata' => ['nullable', 'array'],
        ]);

        $sessionId = $validated['session'] ?? $request->session()->getId();
        $history = $validated['history'] ?? [];
        $question = $validated['message'];
        $recentHistory = collect($history)
            ->map(function (array $item) {
                return [
                    'role' => $item['role'] ?? null,
                    'content' => $item['content'] ?? null,
                ];
            })
            ->filter(fn (array $item) => filled($item['role']) && filled($item['content']))
            ->values()
            ->take(-12)
            ->all();

        $metadata = array_merge(
            [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->headers->get('referer'),
                'language' => $request->getPreferredLanguage(),
                'source' => 'laravel-chatbot-embed',
            ],
            $validated['metadata'] ?? []
        );

        $historyJson = json_encode($recentHistory, JSON_UNESCAPED_UNICODE);
        $historyText = collect($recentHistory)
            ->map(fn ($item) => ($item['role'] ?? 'user') . ': ' . ($item['content'] ?? ''))
            ->filter()
            ->implode("\n");

        $coreBody = [
            'sessionId' => $sessionId,
            'action' => 'sendMessage',
            'chatInput' => $question,
        ];

        $payload = array_merge($coreBody, [
            'message' => $question,
            'query' => $question,
            'input' => $question,
            'question' => $question,
            'prompt' => $question,
            'text' => $question,
            'session' => $sessionId,
            'history' => $recentHistory,
            'messages' => $recentHistory,
            'chat_history' => $recentHistory,
            'chatHistory' => $recentHistory,
            'context' => $recentHistory,
            'conversation' => [
                'sessionId' => $sessionId,
                'messages' => $recentHistory,
            ],
            'history_json' => $historyJson,
            'messages_json' => $historyJson,
            'history_text' => $historyText,
            'metadata' => $metadata,
            'data' => [
                'message' => $question,
                'history' => $recentHistory,
                'history_json' => $historyJson,
                'history_text' => $historyText,
                'session' => $sessionId,
                'metadata' => $metadata,
            ],
            'body' => array_merge($coreBody, [
                'history' => $recentHistory,
                'history_json' => $historyJson,
                'history_text' => $historyText,
                'metadata' => $metadata,
            ]),
        ]);

        if (is_array($settings->extra_options)) {
            $payload['options'] = $settings->extra_options;
        }

        try {
            $http = Http::timeout(max(15, (int) $settings->request_timeout))
                ->asJson()
                ->acceptJson();

            $headers = $settings->buildAuthHeaders();
            if (! empty($headers)) {
                $http = $http->withHeaders($headers);
            }

            if (config('app.debug')) {
                Log::debug('Chatbot webhook request', [
                    'endpoint' => $settings->endpoint,
                    'payload' => $payload,
                ]);
            }

            $response = $http->post($settings->endpoint, $payload);
        } catch (Throwable $exception) {
            Log::error('Chatbot webhook error', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal menghubungi layanan chatbot.',
            ], 504);
        }

        if (! $response->successful()) {
            Log::warning('Chatbot webhook non-200 response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'message' => 'Respon tidak valid dari layanan chatbot.',
            ], 502);
        }

        $body = $response->json();
        if (config('app.debug')) {
            Log::debug('Chatbot webhook response', [
                'status' => $response->status(),
                'body' => $body,
            ]);
        }
        $reply = Arr::get($body, 'reply')
            ?? Arr::get($body, 'data.reply')
            ?? Arr::get($body, 'result.answer')
            ?? Arr::get($body, 'result')
            ?? Arr::get($body, 'text')
            ?? Arr::get($body, 'answer')
            ?? $response->body();

        $responseBody = ['reply' => $reply];

        if (config('app.debug')) {
            $responseBody['raw'] = $body;
        }

        return response()->json($responseBody);
    }
}
