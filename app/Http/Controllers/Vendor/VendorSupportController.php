<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Services\SupportSocketBroadcast;
use App\Support\PlatformSettings;
use App\Support\SocketSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VendorSupportController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $vendor = Auth::user()->currentVendor();
        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $conversation = $this->resolveConversation((int) $vendor->id, (int) Auth::id());
        $conversation->syncTicketStatusFromLastMessage();
        $conversation->refresh();

        $messages = $conversation->orderedMessages()
            ->get()
            ->map(fn (SupportMessage $m) => $m->toBroadcastArray());

        $supportPhone = trim((string) PlatformSettings::get('support_phone', ''));
        $supportEmail = trim((string) PlatformSettings::get('support_email', ''));

        $socket = SocketSupport::chatConnection($conversation->id, (int) Auth::id(), 'vendor');

        return view('vendor.support.index', [
            'conversation' => $conversation,
            'ticketStatus' => $conversation->resolveTicketStatus(),
            'messages' => $messages,
            'socketUrl' => $socket['socketUrl'],
            'socketToken' => $socket['socketToken'],
            'socketConfigured' => $socket['socketConfigured'],
            'supportPhone' => $supportPhone,
            'supportEmail' => $supportEmail,
            'whatsappUrl' => $this->whatsappUrl($supportPhone),
        ]);
    }

    public function storeMessage(Request $request): JsonResponse
    {
        $vendor = Auth::user()->currentVendor();
        if (! $vendor) {
            return response()->json(['success' => false, 'message' => 'Please select a vendor'], 403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = $this->resolveConversation((int) $vendor->id, (int) Auth::id());

        $message = $conversation->messages()->create([
            'sender_type' => SupportMessage::SENDER_VENDOR,
            'sender_id' => Auth::id(),
            'body' => trim($validated['body']),
        ]);

        SupportSocketBroadcast::message($message);

        $conversation->refresh();

        return response()->json([
            'success' => true,
            'message' => $message->toBroadcastArray(),
            'ticket_status' => $conversation->resolveTicketStatus(),
        ]);
    }

    public function socketToken(): JsonResponse
    {
        $vendor = Auth::user()->currentVendor();
        if (! $vendor) {
            return response()->json(['success' => false], 403);
        }

        $conversation = $this->resolveConversation((int) $vendor->id, (int) Auth::id());

        $socket = SocketSupport::chatConnection($conversation->id, (int) Auth::id(), 'vendor');

        return response()->json([
            'token' => $socket['socketToken'],
        ]);
    }

    private function resolveConversation(int $vendorId, int $userId): SupportConversation
    {
        return SupportConversation::query()->firstOrCreate(
            ['vendor_id' => $vendorId],
            ['user_id' => $userId, 'status' => 'open']
        );
    }

    private function whatsappUrl(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return null;
        }
        if (strlen($digits) === 10) {
            $digits = '91'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }
}
