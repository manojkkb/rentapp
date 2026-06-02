<?php

namespace App\Support;

class SupportSocketToken
{
    /**
     * @return array{conversation_id: int, user_id: int, role: string, exp: int}|null
     */
    public static function verify(string $token): ?array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$encoded, $signature] = $parts;
        $secret = (string) config('services.socket.secret', '');
        if ($secret === '') {
            return null;
        }

        $expected = hash_hmac('sha256', $encoded, $secret);
        if (! hash_equals($expected, $signature)) {
            return null;
        }

        $json = base64_decode(strtr($encoded, '-_', '+/'), true);
        if ($json === false) {
            return null;
        }

        /** @var array<string, mixed>|null $payload */
        $payload = json_decode($json, true);
        if (! is_array($payload) || ! isset($payload['conversation_id'], $payload['exp'])) {
            return null;
        }

        if ((int) $payload['exp'] < time()) {
            return null;
        }

        return [
            'conversation_id' => (int) $payload['conversation_id'],
            'user_id' => (int) ($payload['user_id'] ?? 0),
            'role' => (string) ($payload['role'] ?? 'vendor'),
            'exp' => (int) $payload['exp'],
        ];
    }

    public static function generate(int $conversationId, int $userId, string $role = 'vendor'): string
    {
        $payload = json_encode([
            'conversation_id' => $conversationId,
            'user_id' => $userId,
            'role' => $role,
            'exp' => now()->addHours(12)->timestamp,
        ], JSON_THROW_ON_ERROR);

        $encoded = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
        $secret = (string) config('services.socket.secret', '');
        if ($secret === '') {
            return '';
        }

        $signature = hash_hmac('sha256', $encoded, $secret);

        return $encoded.'.'.$signature;
    }
}
