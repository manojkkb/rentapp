<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Http\Controllers\Vendor\VendorSupportController;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Services\SupportSocketBroadcast;
use App\Support\PlatformSettings;
use App\Support\SocketSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends ApiController
{
    use ResolvesApiVendor;

    public function show(): JsonResponse
    {
        $this->requirePermission('support.view');
        $vendor = $this->vendor();
        $user = $this->user();

        $conversation = SupportConversation::query()->firstOrCreate(
            ['vendor_id' => $vendor->id],
            ['user_id' => $user->id, 'status' => 'open']
        );

        $conversation->syncTicketStatusFromLastMessage();
        $conversation->refresh();

        $messages = $conversation->orderedMessages()
            ->get()
            ->map(fn (SupportMessage $m) => $m->toBroadcastArray());

        $socket = SocketSupport::chatConnection($conversation->id, $user->id, 'vendor');

        return $this->ok([
            'conversation_id' => $conversation->id,
            'ticket_status' => $conversation->resolveTicketStatus(),
            'messages' => $messages,
            'socket' => [
                'url' => $socket['socketUrl'],
                'token' => $socket['socketToken'],
                'configured' => $socket['socketConfigured'],
            ],
            'contact' => [
                'phone' => PlatformSettings::get('support_phone'),
                'email' => PlatformSettings::get('support_email'),
            ],
        ]);
    }

    public function storeMessage(Request $request): JsonResponse
    {
        $this->requirePermission('support.view');

        return app(VendorSupportController::class)->storeMessage($request);
    }

    public function socketToken(): JsonResponse
    {
        $this->requirePermission('support.view');

        return app(VendorSupportController::class)->socketToken();
    }
}
