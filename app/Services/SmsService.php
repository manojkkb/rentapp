<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public static function send(string $number, string $message): bool
    {
        $apiKey     = config('services.sms.api_key');
        $senderId   = config('services.sms.sender_id');
        $entityId   = config('services.sms.entity_id');
        $route      = config('services.sms.route');
        $templateId = config('services.sms.template_id');

        $params = [
            'APIKey'        => $apiKey,
            'senderid'      => $senderId,
            'channel'       => 2,
            'DCS'           => 0,
            'flashsms'      => 0,
            'number'        => $number,
            'text'          => $message,
            'route'         => $route,
            'EntityId'      => $entityId,
            'dlttemplateid' => $templateId,
        ];

        try {

            $response = Http::timeout(10)->get(
                'http://www.smsgatewayhub.com/api/mt/SendSMS',
                $params
            );

            if ($response->successful()) {
                Log::info('SMS Sent', [
                    'number' => $number,
                    'response' => $response->body()
                ]);
                return true;
            }

            Log::error('SMS Failed', [
                'number' => $number,
                'response' => $response->body()
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('SMS Exception', [
                'number' => $number,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}