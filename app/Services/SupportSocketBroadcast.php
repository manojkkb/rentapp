<?php

namespace App\Services;

use App\Models\SupportMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupportSocketBroadcast
{
    public static function message(SupportMessage $message): void
    {
        $url = config('services.socket.broadcast_url');
        $secret = config('services.socket.secret');

        if (! $url || ! $secret) {
            return;
        }

        try {
            Http::timeout(3)
                ->withHeaders(['X-Socket-Secret' => $secret])
                ->post($url, [
                    'conversation_id' => $message->support_conversation_id,
                    'message' => $message->toBroadcastArray(),
                ]);
        } catch (\Throwable $e) {
            Log::warning('Support socket broadcast failed', [
                'conversation_id' => $message->support_conversation_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
