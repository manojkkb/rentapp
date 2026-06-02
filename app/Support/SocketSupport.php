<?php

namespace App\Support;

class SocketSupport
{
    public static function isConfigured(): bool
    {
        return self::secret() !== '' && self::url() !== '';
    }

    public static function secret(): string
    {
        return (string) config('services.socket.secret', '');
    }

    public static function url(): string
    {
        return rtrim((string) config('services.socket.url', ''), '/');
    }

    /**
     * @return array{socketUrl: string, socketToken: string, socketConfigured: bool}
     */
    public static function chatConnection(int $conversationId, int $userId, string $role): array
    {
        if (! self::isConfigured()) {
            return [
                'socketUrl' => '',
                'socketToken' => '',
                'socketConfigured' => false,
            ];
        }

        return [
            'socketUrl' => self::url(),
            'socketToken' => SupportSocketToken::generate($conversationId, $userId, $role),
            'socketConfigured' => true,
        ];
    }
}
